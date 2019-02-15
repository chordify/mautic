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
        $results = $this->markAsRead($leadId, [$inboxItemId]);
        if (is_array($results)) {
            $view = $this->view($results[0], Codes::HTTP_OK);

            return $this->handleView($view);
        }

        return $results;
    }

    /**
     * Mark many messages of a lead as read. Should send the
     * ids as POST request.
     *
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function inboxSetReadManyAction($leadId)
    {
        if (!$this->request->request->has('ids') || !is_array($this->request->request->get('ids'))) {
            return $this->badRequest('POST parameter \'ids\' missing or not an array');
        }

        $results = $this->markAsRead($leadId, $this->request->request->get('ids'));
        if (is_array($results)) {
            $view = $this->view($results, Codes::HTTP_OK);

            return $this->handleView($view);
        }

        return $results;
    }

    /**
     * Internal function to mark many inbox items as read.
     */
    private function markAsRead($leadId, $inboxItemIds)
    {
        // Sanitize
        $inboxItemIds = array_unique($inboxItemIds);

        // Get the lead
        $leadModel = $this->getModel('lead');
        $lead      = $leadModel->getEntity($leadId);
        if (null === $lead) {
            return $this->notFound();
        }

        // Get the inboxitems
        $inboxRepo  = $this->model->getInboxRepository();
        $inboxItems = $inboxRepo->getEntities(['ids' => $inboxItemIds, 'ignore_paginator' => true]);
        if (count($inboxItems) != count($inboxItemIds)) {
            return $this->notFound();
        }

        // Verify that the lead matches the inbox item ids
        foreach ($inboxItems as $inboxItem) {
            if ($lead->getId() != $inboxItem->getContact()->getId()) {
                return $this->badRequest();
            }
        }

        // Set read date to now
        foreach ($inboxItems as $inboxItem) {
            $inboxItem->setDateRead(new \DateTime());
            $inboxRepo->saveEntity($inboxItem);

            // And return without contact info
            $inboxItem->setContact(null);
        }

        return $inboxItems;
    }

    /**
     * Hide the message of a lead.
     *
     * @param int $leadId      Lead ID
     * @param int $inboxItemId The inbox item ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function inboxSetHideAction($leadId, $inboxItemId)
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

        // Set hide date to now and save
        $inboxItem->setDateHidden(new \DateTime());
        $inboxRepo->saveEntity($inboxItem);

        // And return successfully
        return $this->handleView($this->view([], Codes::HTTP_OK));
    }
}
