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

class AmazonSNSApi extends AbstractNotificationApi
{

    /**
     * @param string|array $playerIds     Player ID as string, or an array of player ID's
     * @param Notification $notification
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendNotification($playerIds, Notification $notification)
    {
        if (!is_array($playerIds)) {
            $playerIds = [$playerIds];
        }
		
        $apiKeys    = $this->integrationHelper->getIntegrationObject('AmazonSNS')->getKeys();
		$keyID      = $apiKeys['key'];
		$keySecret  = $apiKeys['key_secret'];
		$region     = $apiKeys['region'];
		$appArn     = $apiKeys['application_arn'];


		$message = [ 'default' => $notification->getMessage() ];
		if( true ) { // TODO: check if iOS
			$message['APNS_SANDBOX'] = json_encode(
				[ 'aps' => [ 'alert' => $notification->getMessage(),
							 'sound' => 'default' ]
				]);
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
				$endpoint = $client->createPlatformEndpoint(['PlatformApplicationArn' => $appArn,
															 'Token' => $playerId]);
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
}
