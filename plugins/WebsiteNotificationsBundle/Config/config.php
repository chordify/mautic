<?php

return [
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
