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

use Aws\Sns\SnsClient;
use Aws\Common\Credentials\Credentials;
use Mautic\NotificationBundle\Entity\Notification;
use Mautic\NotificationBundle\Entity\PushID;

class AmazonSNSApi extends AbstractNotificationApi
{

    /**
     * @param array        $playerIds     Array of pushID's
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

        $message = [ 'default' => $notification->getMessage() ];
        if ($notification->isMobile()) {
            $this->addMobileData($message, $notification);
        }
        $messageData = [ 'MessageStructure' => 'json',
                         'Subject' => $notification->getHeading(),
                         'Message' => json_encode($message) ];

        $credentials = new Credentials($keyID, $keySecret);
        $client = SnsClient::factory(['credentials' => $credentials,
                                      'region' => $region,
                                      'version' => 'latest']);

        foreach($playerIds as $playerId) {
            try {
                $appArn = null;
                switch($playerId->getType()) {
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
                $endpoint = $client->createPlatformEndpoint(['PlatformApplicationArn' => $appArn,
                                                             'Token' => $playerId->getPushID()]);
            } catch( \Exception $e ){
                if (MAUTIC_ENV === 'dev') {
                    print "Warning: " . $e->getMessage() . "\n";
                }
                return false;
            }

            $messageData['TargetArn'] = $endpoint['EndpointArn'];
            $published = $client->publish($messageData);
        }
        return true;
    }

    private function addMobileData(array &$message, Notification $notification) {
        $mobileConfig = $notification->getMobileSettings();

        // iOS fields
        $apsFields = [
			'alert' => [
				'title' => $notification->getHeading(),
				'body' => $notification->getMessage(),
			],
		];
        $apsFields['sound'] = empty($mobileConfig['ios_sound']) ? 'default' : $mobileConfig['ios_sound'];
        if( isset($mobileConfig['ios_badgeCount']) ) {
            $apsFields['badge'] = (int) $mobileConfig['ios_badgeCount'];
        }
        $iosFields = [
            'aps' => $apsFields,
            'notification_id' => $notification->getId(),
        ];
        $message['APNS_SANDBOX'] = json_encode($iosFields);
        $message['APNS'] = json_encode($iosFields);

        // Android fields
        $androidFields = [
            'data' => [
                'notification_id' => $notification->getId(),
            ],
        ];
        $message['GCM'] = json_encode($androidFields);
    }
}
