<?php

namespace MauticPlugin\WebsiteNotificationsBundle;

/**
 * Class WebsiteNotificationsEvents
 * Events available for WebsiteNotificationsBundle.
 */
final class WebsiteNotificationsEvents
{
    /**
     * The mautic.website_notifications.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Mautic\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'mautic.website_notifications.on_campaign_trigger_action';
}
