<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;

/**
 * Class InboxItemRepository.
 */
class InboxItemRepository extends CommonRepository
{
    public function getLeadInbox(Lead $lead, $onlyUnread = false)
    {
        $q = $this->_em
           ->createQueryBuilder()
           ->select('e, i')
           ->from('WebsiteNotificationsBundle:InboxItem', 'i')
           ->innerJoin('i.notification', 'e');
        $q->andWhere($q->expr()->eq('i.contact', (int) $lead->getId()));

        if ($onlyUnread) {
            $q->andWhere($q->expr()->isNull('i.dateRead'));
        }

        $args['qb'] = $q;

        return parent::getEntities($args);
    }
}
