<?php

namespace MauticPlugin\WebsiteNotificationsBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\Event\SubmissionEvent;
use Mautic\FormBundle\FormEvents;
use MauticPlugin\WebsiteNotificationsBundle\Model\WebsiteNotificationsModel;
use MauticPlugin\WebsiteNotificationsBundle\WebsiteNotificationsEvents;

/**
 * Class FormSubscriber.
 */
class FormSubscriber extends CommonSubscriber
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
            FormEvents::FORM_ON_BUILD                       => ['onFormBuild', 0],
            WebsiteNotificationsEvents::ON_FORM_SEND_ACTION => ['onFormSendAction', 0],
        ];
    }

    public function onFormBuild(FormBuilderEvent $event)
    {
        $event->addSubmitAction(
            'website_notifications.send_website_notification',
            [
                'label'             => 'mautic.website_notifications.campaign.send_website_notification',
                'group'             => 'mautic.website_notifications',
                'description'       => 'mautic.website_notifications.campaign.send_website_notification.tooltip',
                'eventName'         => WebsiteNotificationsEvents::ON_FORM_SEND_ACTION,
                'formType'          => 'website_notification_send',
                'formTheme'         => 'WebsiteNotificationsBundle:FormTheme\WebsiteNotificationSend',
                'allowCampaignForm' => true,
            ]
        );
    }

    public function onFormSendAction(SubmissionEvent $event)
    {
        // Get the lead
        $lead = $event->getSubmission()->getLead();

        // Get the notification
        $notificationId = (int) $event->getActionConfig()['website_notification'];
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
