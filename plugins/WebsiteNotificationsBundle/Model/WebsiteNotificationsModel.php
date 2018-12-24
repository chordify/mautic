<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Model;

use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\CoreBundle\Model\TranslationModelTrait;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Helper\TokenHelper;
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

        $results = [];
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

            // Replace the lead fields (also in the url's, where we may want to use the mongoid)
            $notification = $item->getNotification();
            $newTitle     = TokenHelper::findLeadTokens($notification->getTitle(), $lead->getProfileFields(), true);
            $notification->setTitle($newTitle);
            $newMessage = TokenHelper::findLeadTokens($notification->getMessage(), $lead->getProfileFields(), true);
            $notification->setMessage($newMessage);
            $newUrl = TokenHelper::findLeadTokens($notification->getUrl(), $lead->getProfileFields(), true);
            $notification->setUrl($newUrl);
            $newImage = TokenHelper::findLeadTokens($notification->getImage(), $lead->getProfileFields(), true);
            $notification->setImage($newImage);
            $newButtonText = TokenHelper::findLeadTokens($notification->getButtonText(), $lead->getProfileFields(), true);
            $notification->setButtonText($newButtonText);
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

    /**
     * @param           $notification
     * @param           $unit
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array
     */
    public function getWebsiteNotificationStats($notification, $unit, \DateTime $dateFrom, \DateTime $dateTo)
    {
        if (!$notification instanceof WebsiteNotification) {
            $notification = $this->getEntity($notification);
        }

        $filter = [
            'notification_id' => [$notification->getId()],
        ];

        return $this->getWebsiteNotificationsLineChartData($unit, $dateFrom, $dateTo, null, $filter);
    }

    /**
     * Get line chart data of website notifications sent and read.
     *
     * @param char      $unit          {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string    $dateFormat
     * @param array     $filter
     * @param bool      $canViewOthers
     *
     * @return array
     */
    public function getWebsiteNotificationsLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [], $canViewOthers = true)
    {
        $datasets   = [];
        $flag       = null;
        $companyId  = null;
        $campaignId = null;
        $segmentId  = null;

        $chart = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);

        // Sent
        $q = $query->prepareTimeDataQuery('website_notifications_inbox', 'date_sent', $filter);
        if (!$canViewOthers) {
            $this->limitQueryToCreator($q);
        }
        $data = $query->loadAndBuildTimeData($q);
        $chart->setDataset($this->translator->trans('mautic.website_notifications.stats.sent'), $data);

        // Read
        $q = $query->prepareTimeDataQuery('website_notifications_inbox', 'date_read', $filter);
        if (!$canViewOthers) {
            $this->limitQueryToCreator($q);
        }
        $data = $query->loadAndBuildTimeData($q);
        $chart->setDataset($this->translator->trans('mautic.website_notifications.stats.read'), $data);

        // Hidden
        $q = $query->prepareTimeDataQuery('website_notifications_inbox', 'date_hidden', $filter);
        if (!$canViewOthers) {
            $this->limitQueryToCreator($q);
        }
        $data = $query->loadAndBuildTimeData($q);
        $chart->setDataset($this->translator->trans('mautic.website_notifications.stats.hidden'), $data);

        return $chart->render();
    }
}
