<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead;

/**
 * Class InboxItem.
 */
class InboxItem
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var WebsiteNotification
     */
    private $notification;

    /**
     * @var \Mautic\LeadBundle\Entity\Lead
     */
    private $contact;

    /**
     * @var \DateTime
     */
    private $dateSent;

    /**
     * @var \DateTime
     */
    private $dateRead;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('website_notifications_inbox')
            ->setCustomRepositoryClass('MauticPlugin\WebsiteNotificationsBundle\Entity\InboxItemRepository');

        $builder->addId();

        $builder->createManyToOne('notification', 'WebsiteNotification')
            ->addJoinColumn('notification_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->addContact();

        $builder->createField('dateSent', 'datetime')
            ->columnName('date_sent')
            ->build();

        $builder->createField('dateRead', 'datetime')
            ->columnName('date_read')
            ->nullable()
            ->build();
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('inbox')
            ->addProperties(
                [
                    'id',
                    'dateSent',
                    'dateRead',
                    'contact',
                    'notification',
                ]
            )
            ->build();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Lead
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param mixed $lead
     */
    public function setContact(Lead $contact = null)
    {
        $this->contact = $contact;
    }

    /**
     * @return WebsiteNotification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param WebsiteNotification $notification
     */
    public function setNotification(WebsiteNotification $notification = null)
    {
        $this->notification = $notification;
    }

    /**
     * @return mixed
     */
    public function getDateSent()
    {
        return $this->dateSent;
    }

    /**
     * @param mixed $dateSent
     */
    public function setDateSent($dateSent)
    {
        $this->dateSent = $dateSent;
    }

    /**
     * @return mixed
     */
    public function getDateRead()
    {
        return $this->dateRead;
    }

    /**
     * @param mixed $dateRead
     */
    public function setDateRead($dateRead)
    {
        $this->dateRead = $dateRead;
    }
}
