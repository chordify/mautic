<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
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

    /**
     * Get the inbox of a lead.
     *
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function inboxAction($leadId, $onlyUnread = false)
    {
        // Get the lead
        $leadModel = $this->getModel('lead');
        $lead      = $leadModel->getEntity($leadId);

        if (null === $lead) {
            return $this->notFound();
        }

        // Get the messages for this lead
        $messages = $this->model->getLeadInbox($lead, $onlyUnread);

        // And return them
        $view = $this->view($messages, Codes::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Get the inbox of a lead.
     *
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function inboxUnreadAction($leadId)
    {
        return $this->inboxAction($leadId, true);
    }

    /**
     * Get the inbox of a lead.
     *
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function inboxUnreadCountAction($leadId)
    {
        // Get the lead
        $leadModel = $this->getModel('lead');
        $lead      = $leadModel->getEntity($leadId);

        if (null === $lead) {
            return $this->notFound();
        }

        // Get the number of messages for this lead
        $unread = $this->model->getLeadUnreadCount($lead);

        // And return them
        $view = $this->view(['unread' => $unread], Codes::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Mark a message of a lead as read.
     *
     * @param int $leadId      Lead ID
     * @param int $inboxItemId The inbox item ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function inboxSetReadAction($leadId, $inboxItemId)
    {
        // Get the lead
        $leadModel = $this->getModel('lead');
        $lead      = $leadModel->getEntity($leadId);

        if (null === $lead) {
            return $this->notFound();
        }

        // Get the inboxitem
        $inboxRepo = $this->model->getInboxRepository();
        $inboxItem = $inboxRepo->getEntity($inboxItemId);
        if (null === $inboxItem) {
            return $this->notFound();
        }

        // Verify that the lead matches the inbox item id
        if ($lead->getId() != $inboxItem->getContact()->getId()) {
            return $this->badRequest();
        }

        // Set read date to now and save
        $inboxItem->setDateRead(new \DateTime());
        $inboxRepo->saveEntity($inboxItem);

        // And return successfully (without contact info)
        $inboxItem->setContact(null);
        $view = $this->view($inboxItem, Codes::HTTP_OK);

        return $this->handleView($view);
    }
}
