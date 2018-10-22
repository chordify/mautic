<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\WebsiteNotificationsBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\WebsiteNotificationsBundle\Model\WebsiteNotificationsModel;
use MauticPlugin\WebsiteNotificationsBundle\WebsiteNotificationsEvents;

/**
 * Class CampaignSubscriber.
 */
class CampaignSubscriber extends CommonSubscriber
{
    /**
     * @var WebsiteNotificationsModel
     */
    protected $websiteNotificationsModel;

    /**
     * CampaignSubscriber constructor.
     *
     * @param WebsiteNotificationsModel $websiteNotificationsModel
     */
    public function __construct(
        WebsiteNotificationsModel $websiteNotificationsModel
    ) {
        $this->websiteNotificationsModel = $websiteNotificationsModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD                      => ['onCampaignBuild', 0],
            WebsiteNotificationsEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $event->addAction(
            'website_notifications.send_website_notification',
            [
                'label'            => 'mautic.website_notifications.campaign.send_website_notification',
                'description'      => 'mautic.website_notifications.campaign.send_website_notification.tooltip',
                'eventName'        => WebsiteNotificationsEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'         => 'website_notification_send',
                'formTheme'        => 'WebsiteNotificationsBundle:FormTheme\WebsiteNotificationSend',
            ]
        );
    }

    /**
     * @param CampaignExecutionEvent $event
     *
     * @return CampaignExecutionEvent
     */
    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        // Get the lead
        $lead = $event->getLead();

        // Get the notification
        $notificationId = (int) $event->getConfig()['website_notification'];
        $notification   = $this->websiteNotificationsModel->getEntity($notificationId);

        if ($notification->getId() !== $notificationId) {
            return $event->setFailed('mautic.notification.campaign.failed.missing_entity');
        }

        if (!$notification->getIsPublished()) {
            return $event->setFailed('mautic.notification.campaign.failed.unpublished');
        }

        // Send the notification to the lead
        $this->websiteNotificationsModel->sendWebsiteNotification($notification, $lead);
    }
}
