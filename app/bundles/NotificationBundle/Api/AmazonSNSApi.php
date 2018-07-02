<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\NotificationBundle\Api;

use Aws\Common\Credentials\Credentials;
use Aws\Sns\Exception\NotFoundException;
use Aws\Sns\SnsClient;
use Mautic\NotificationBundle\Entity\Notification;
use Mautic\NotificationBundle\Entity\PushID;

class AmazonSNSApi extends AbstractNotificationApi
{
    /**
     * @param array        $playerIds    Array of pushID's
     * @param Notification $notification
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendNotification(array $playerIds, Notification $notification)
    {
        $integration     = $this->integrationHelper->getIntegrationObject('AmazonSNS');
        $apiKeys         = $integration->getKeys();
        $settings        = $integration->getIntegrationSettings();
        $featureSettings = $settings->getFeatureSettings();
        $keyID           = $apiKeys['key'];
        $keySecret       = $apiKeys['key_secret'];
        $region          = $apiKeys['region'];

        $message = ['default' => $notification->getMessage()];
        if ($notification->isMobile()) {
            $this->addMobileData($message, $notification);
        }
        $messageData = ['MessageStructure' => 'json',
                         'Subject'         => $notification->getHeading(),
                         'Message'         => json_encode($message), ];

        $credentials = new Credentials($keyID, $keySecret);
        $client      = SnsClient::factory(['credentials' => $credentials,
                                      'region'           => $region,
                                      'version'          => 'latest', ]);

        $sent = false;
        foreach ($playerIds as $playerId) {
            if (!$this->ensureArn($featureSettings, $client, $playerId)) {
                $model = $this->factory->getModel('lead.lead');
                $model->removePushIDFromLead($playerId->getLead(), $playerId);
                continue;
            }

            $messageData['TargetArn'] = $playerId->getAmazonArn();
            $published                = $client->publish($messageData);
            if (MAUTIC_ENV === 'dev') {
                print_r($messageData);
            }
            $sent = true;
        }

        return $sent;
    }

    private function ensureArn($featureSettings, SnsClient $client, PushID $pushId)
    {
        $em         = $this->factory->get('doctrine.orm.entity_manager');
        $pushIDRepo = $em->getRepository(PushID::class);

        if ($pushId->getAmazonArn() == null) {
            // Get the App Arn
            $appArn = null;
            switch ($pushId->getType()) {
            case PushID::TYPE_APPLE_DEV:
                $appArn = $featureSettings['apple_dev_arn'];
                break;
            case PushID::TYPE_APPLE_LIVE:
                $appArn = $featureSettings['apple_live_arn'];
                break;
            case PushID::TYPE_GCM:
                $appArn = $featureSettings['gcm_arn'];
                break;
            }

            // Get the client Arn
            $endpoint = $client->createPlatformEndpoint(
                [
                    'PlatformApplicationArn' => $appArn,
                    'Token'                  => $pushId->getPushID(),
                ]);

            // Save it
            $pushId->setAmazonArn($endpoint['EndpointArn']);
            if ($pushId->getId() != null) {
                $pushIDRepo->saveEntity($pushId);
            }
        }

        // Check the attributes
        try {
            $attributes = $client->getEndpointAttributes(['EndpointArn' => $pushId->getAmazonArn()]);

            return json_decode($attributes['Attributes']['Enabled']);
        } catch (NotFoundException $e) {
            // Arn not valid anymore
            return false;
        }
    }

    private function addMobileData(array &$message, Notification $notification)
    {
        $mobileConfig = $notification->getMobileSettings();

        // iOS fields
        $apsFields = [
            'alert' => [
                'title' => $notification->getHeading(),
                'body'  => $notification->getMessage(),
            ],
        ];
        $apsFields['sound'] = empty($mobileConfig['ios_sound']) ? 'default' : $mobileConfig['ios_sound'];
        if (isset($mobileConfig['ios_badgeCount'])) {
            $apsFields['badge'] = (int) $mobileConfig['ios_badgeCount'];
        }
        $iosFields = [
            'aps'             => $apsFields,
            'notification_id' => $notification->getId(),
        ];
        $message['APNS_SANDBOX'] = json_encode($iosFields);
        $message['APNS']         = json_encode($iosFields);

        // Android fields
        $androidFields = [
            'data' => [
                'notification_id' => $notification->getId(),
            ],
        ];
        $message['GCM'] = json_encode($androidFields);
    }
}
