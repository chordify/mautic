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

use Joomla\Http\Http;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\NotificationBundle\Entity\Notification;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;

use Mautic\NotificationBundle\Api\OneSignalApi;
use Mautic\NotificationBundle\Api\AmazonSNSApi;

class NotificationDispatchApi extends AbstractNotificationApi
{
    /**
     * @var AbstractNotificationApi
     */
	protected $oneSignal;

    /**
     * @var AbstractNotificationApi
     */
	protected $amazonSNS;

    /**
     * NotificationDispatchApi constructor.
     *
     * @param MauticFactory     $factory
     * @param Http              $http
     * @param TrackableModel    $trackableModel
     * @param IntegrationHelper $integrationHelper
     */
	public function __construct(MauticFactory $factory, Http $http, TrackableModel $trackableModel, IntegrationHelper $integrationHelper) {
		parent::__construct($factory, $http, $trackableModel, $integrationHelper);
		$this->oneSignal = new OneSignalApi($factory, $http, $trackableModel, $integrationHelper);
		$this->amazonSNS = new AmazonSNSApi($factory, $http, $trackableModel, $integrationHelper);
	}
	
    /**
     * @param              $id
     * @param Notification $notification
     *
     * @return bool
     */
	public function sendNotification($id, Notification $notification) {
		$integrationA = $this->integrationHelper->getIntegrationObject('AmazonSNS');
		$integrationO = $this->integrationHelper->getIntegrationObject('OneSignal');

		if($integrationA && $integrationA->getIntegrationSettings()->getIsPublished() !== false && $notification->isMobile()) {
			print "Amazon\n";
			return $this->amazonSNS->sendNotification($id, $notification);
		} else if($integrationO && $integrationO->getIntegrationSettings()->getIsPublished() !== false
				  && ($notification->isMobile() && in_array('mobile', $integrationO->getFeatures()))
				  || (!$notification->isMobile() && in_array('desktop', $integrationO->getFeatures()))) {
			print "OneSignal\n";
			return $this->oneSignal->sendNotification($id, $notification);
		} else {
			print "No choice\n";
			throw new Exception(); // todo
		}
	}
}