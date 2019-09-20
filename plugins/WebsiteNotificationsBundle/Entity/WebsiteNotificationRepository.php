<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Entity;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class WebsiteNotificationRepository.
 */
class WebsiteNotificationRepository extends CommonRepository
{
    /**
     * Get a list of entities.
     *
     * @param array $args
     *
     * @return Paginator
     */
    public function getEntities(array $args = [])
    {
        $q = $this->_em
           ->createQueryBuilder()
           ->select('e')
           ->from('WebsiteNotificationsBundle:WebsiteNotification', 'e', 'e.id');
        if (empty($args['iterator_mode'])) {
            $q->leftJoin('e.category', 'c');
        }

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    public function getWebsiteNotificationList($search = '', $limit = 10, $start = 0, $viewOther = false, $topLevel = false, array $ignoreIds = [])
    {
        $q = $this->createQueryBuilder('e');
        $q->select('partial e.{id, name, language}');

        if (!empty($search)) {
            if (is_array($search)) {
                $search = array_map('intval', $search);
                $q->andWhere($q->expr()->in('e.id', ':search'))
                    ->setParameter('search', $search);
            } else {
                $q->andWhere($q->expr()->like('e.name', ':search'))
                    ->setParameter('search', "%{$search}%");
            }
        }

        if (!$viewOther) {
            $q->andWhere($q->expr()->eq('e.createdBy', ':id'))
                ->setParameter('id', $this->currentUser->getId());
        }

        if ($topLevel == 'translation') {
            //only get top level pages
            $q->andWhere($q->expr()->isNull('e.translationParent'));
        }

        if (!empty($ignoreIds)) {
            $q->andWhere($q->expr()->notIn('e.id', ':ignoreIds'))
                ->setParameter('ignoreIds', $ignoreIds);
        }

        $q->orderBy('e.name');

        return $q->getQuery()->getArrayResult();
    }
}
