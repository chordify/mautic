<?php

return [
    'services' => [
        'events' => [
            'mautic.website_notifications.campaignbundle.subscriber' => [
                'class'     => 'MauticPlugin\WebsiteNotificationsBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.website_notifications.model.website_notifications',
                ],
            ],
    ],
        'models' => [
            'mautic.website_notifications.model.website_notifications' => [
                'class'     => 'MauticPlugin\WebsiteNotificationsBundle\Model\WebsiteNotificationsModel',
                'arguments' => [
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.website_notification' => [
                'class'     => 'MauticPlugin\WebsiteNotificationsBundle\Form\Type\WebsiteNotificationType',
                'arguments' => 'mautic.factory',
                'alias'     => 'website_notification',
            ],
            'mautic.form.type.website_notification_list' => [
                'class' => 'MauticPlugin\WebsiteNotificationsBundle\Form\Type\WebsiteNotificationListType',
                'alias' => 'website_notification_list',
            ],

            'mautic.form.type.website_notification_send' => [
                'class' => 'MauticPlugin\WebsiteNotificationsBundle\Form\Type\WebsiteNotificationSendType',
                'alias' => 'website_notification_send',
            ],
        ],
    ],
    'routes' => [
        'main' => [
            'website_notifications_index' => [
                'path'       => '/website_notifications/{page}',
                'controller' => 'WebsiteNotificationsBundle:WebsiteNotifications:index',
            ],
            'website_notifications_action' => [
                'path'       => '/website_notifications/{objectAction}/{objectId}',
                'controller' => 'WebsiteNotificationsBundle:WebsiteNotifications:execute',
            ],
        'mautic_website_notifications_contacts' => [
                'path'       => '/website_notifications/contacts/{objectId}',
                'controller' => 'WebsiteNotificationsBundle:WebsiteNotifications:contacts',
            ],
        ],
        'api' => [
            'mautic_api_websitenotificationsstandard' => [
                'standard_entity' => true,
                'name'            => 'website_notifications',
                'path'            => '/website_notifications',
                'controller'      => 'WebsiteNotificationsBundle:Api\WebsiteNotificationsApi',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mautic.website_notifications.notifications' => [
                    'parent' => 'mautic.core.channels',
                    'access' => ['website_notifications:website_notifications:viewown', 'website_notifications:website_notifications:viewother'],
                    'route'  => 'website_notifications_index',
                ],
            ],
        ],
    ],
];
