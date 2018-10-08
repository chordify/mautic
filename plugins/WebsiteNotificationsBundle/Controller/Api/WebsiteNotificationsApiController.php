<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Controller\Api;

use Mautic\ApiBundle\Controller\CommonApiController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class WebsiteNotificationsApiController.
 */
class WebsiteNotificationsApiController extends CommonApiController
{
    /**
     * {@inheritdoc}
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->model           = $this->getModel('website_notifications');
        $this->entityClass     = 'MauticPlugin\WebsiteNotificationsBundle\Entity\WebsiteNotification';
        $this->entityNameOne   = 'website_notification';
        $this->entityNameMulti = 'website_notifications';

        parent::initialize($event);
    }
}
