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
}
