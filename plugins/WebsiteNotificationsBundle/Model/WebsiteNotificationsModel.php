<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Model;

use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\CoreBundle\Model\TranslationModelTrait;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\WebsiteNotificationsBundle\Entity\InboxItem;
use MauticPlugin\WebsiteNotificationsBundle\Entity\WebsiteNotification;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class WebsiteNotificationsModel extends FormModel implements AjaxLookupModelInterface
{
    use TranslationModelTrait;

    public function getRepository()
    {
        return $this->em->getRepository('WebsiteNotificationsBundle:WebsiteNotification');
    }

    public function getPermissionBase()
    {
        return 'website_notifications:website_notifications';
    }

    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof WebsiteNotification) {
            throw new MethodNotAllowedHttpException(['WebsiteNotification']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('website_notification', $entity, $options);
    }

    public function getEntity($id = null)
    {
        if ($id === null) {
            $entity = new WebsiteNotification();
        } else {
            $entity = parent::getEntity($id);
        }

        return $entity;
    }

    public function getLookupResults($filter = '', $limit = 10, $start = 0, $options = [])
    {
        $entities = $this->getRepository()->getWebsiteNotificationList(
            $filter,
            $limit,
            $start,
            $this->security->isGranted($this->getPermissionBase().':viewother'),
            isset($options['top_level']) ? $options['top_level'] : false,
            isset($options['ignore_ids']) ? $options['ignore_ids'] : []
        );

        foreach ($entities as $entity) {
            $results[$entity['language']][$entity['id']] = $entity['name'];
        }

        //sort by language
        ksort($results);

        return $results;
    }

    /*
     * Get all notifications that are send to a lead
     */
    public function getLeadInbox(Lead $lead, $onlyUnread = false)
    {
        $items = $this->getInboxRepository()->getLeadInbox($lead, $onlyUnread);
        foreach ($items as $item) {
            // We do not want to return the lead
            $item->setContact(null);

            // Use a translation if available
            $notification                           = $item->getNotification();
            list($ignore, $notificationTranslation) = $this->getTranslatedEntity($notification, $lead);
            if ($notificationTranslation !== null) {
                $item->setNotification($notificationTranslation);
            }
        }

        return $items;
    }

    /*
     * Get the number of unread notifications for a lead
     */
    public function getLeadUnreadCount(Lead $lead)
    {
        return $this->getInboxRepository()->getLeadUnreadCount($lead);
    }

    /*
     * Add the notification to a users inbox
     *
     * @param WebsiteNotification $notification The website notification to send
     * @param Lead                $lead         The lead to send it to
     */
    public function sendWebsiteNotification(WebsiteNotification $notification, Lead $lead)
    {
        $msg = new InboxItem();
        $msg->setContact($lead);
        $msg->setNotification($notification);
        $msg->setDateSent(new \DateTime());

        return $this->getInboxRepository()->saveEntity($msg);
    }

    public function getInboxRepository()
    {
        return $this->em->getRepository('WebsiteNotificationsBundle:InboxItem');
    }
}
