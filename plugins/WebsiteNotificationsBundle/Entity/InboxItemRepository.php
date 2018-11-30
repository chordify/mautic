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

        $q->andWhere($q->expr()->isNull('i.dateHidden'));

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    public function getLeadUnreadCount(Lead $lead)
    {
        $q = $this->_em->createQueryBuilder();
        $q->select($q->expr()->count('i.id'))
            ->from('WebsiteNotificationsBundle:InboxItem', 'i')
            ->andWhere($q->expr()->eq('i.contact', (int) $lead->getId()))
            ->andWhere($q->expr()->isNull('i.dateRead'));

        return (int) $q->getQuery()->getSingleScalarResult();
    }
}
