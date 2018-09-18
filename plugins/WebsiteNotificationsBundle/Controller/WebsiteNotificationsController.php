<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;

class WebsiteNotificationsController extends FormController
{
    public function indexAction($page = 1)
    {
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'website_notifications:website_notifications:viewown',
                'website_notifications:website_notifications:viewother',
                'website_notifications:website_notifications:create',
                'website_notifications:website_notifications:editown',
                'website_notifications:website_notifications:editother',
                'website_notifications:website_notifications:deleteown',
                'website_notifications:website_notifications:deleteother',
                'website_notifications:website_notifications:publishown',
                'website_notifications:website_notifications:publishother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['website_notifications:website_notifications:viewown'] && !$permissions['website_notifications:website_notifications:viewother']) {
            return $this->accessDenied();
        }

        // Return the view
        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue' => '',
                    'items'       => 0,
                    'totalItems'  => 0,
                    'page'        => $page,
                    'limit'       => 10,
                    'tmpl'        => $this->request->get('tmpl', 'index'),
                    'permissions' => $permissions,
                    'security'    => $this->get('mautic.security'),
                ],
                'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#website_notifications_index',
                    'mauticContent' => 'website_notifications',
                    'route'         => $this->generateUrl('website_notifications_index', ['page' => $page]),
                ],
            ]
        );
    }

    public function newAction($entity = null)
    {
        return [];
    }
}
