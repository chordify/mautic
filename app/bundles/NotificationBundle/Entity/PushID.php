<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead;

class PushID
{
    /**
     * The different types of Push ID types
     */
	const TYPE_APPLE_DEV  = 0;
	const TYPE_APPLE_LIVE = 1;
	const TYPE_GCM        = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Mautic\LeadBundle\Entity\Lead
     */
    private $lead;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $pushID;

    /**
     * @var string
     */
    private $amazon_arn;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var bool
     */
    private $mobile;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('push_ids')
            ->setCustomRepositoryClass('Mautic\NotificationBundle\Entity\PushIDRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createField('type', 'integer')
            ->columnName('type')
            ->nullable(false)
            ->build();

        $builder->createField('pushID', 'string')
            ->columnName('push_id')
            ->nullable(false)
            ->build();

        $builder->addIndex(['type', 'push_id'], 'push_id_search');

        $builder->createField('amazon_arn', 'string')
            ->length(2048)
            ->nullable(true)
            ->build();

        $builder->createManyToOne('lead', 'Mautic\LeadBundle\Entity\Lead')
            ->addJoinColumn('lead_id', 'id', true, false, 'SET NULL')
            ->inversedBy('pushIds')
            ->build();

        $builder->createField('enabled', 'boolean')->build();
        $builder->createField('mobile', 'boolean')->build();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \Mautic\LeadBundle\Entity\Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead $lead
     *
     * @return $this
     */
    public function setLead(Lead $lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getPushID()
    {
        return $this->pushID;
    }

    /**
     * @param string $pushID
     *
     * @return $this
     */
    public function setPushID($pushID)
    {
        $this->pushID = $pushID;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmazonArn()
    {
        return $this->amazon_arn;
    }

    /**
     * @param string $pushID
     *
     * @return $this
     */
    public function setAmazonArn($arn)
    {
        $this->amazon_arn = $arn;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        return $this->mobile;
    }

    /**
     * @param bool $mobile
     *
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return string
     */
    public function showPushID() {
        $typeStr = self::typeToString($this->type);
        $tokenStr = strlen($this->pushID) < 14
                  ? $this->pushID
                  : substr($this->pushID, 0, 7) . "..." . substr($this->pushID, -7);

        return $typeStr . " " . $tokenStr;
    }

    /**
     * Convert a push ID type to a string representation
     * @return string
     */
    public static function typeToString($type) {
        switch($type) {
        case self::TYPE_APPLE_DEV:
            return 'apple_dev';
        case self::TYPE_APPLE_LIVE:
            return 'apple';
        case self::TYPE_GCM:
            return 'android';
        default:
            throw new \InvalidArgumentException('Unknown notification type ' . $type);
        }
    }

    /**
     * Convert a push ID string to a type int
     * @return int
     */
    public static function typeFromString($str) {
        switch($str) {
        case 'apple_dev':
            return self::TYPE_APPLE_DEV;
        case 'apple':
            return self::TYPE_APPLE_LIVE;
        case 'android':
            return self::TYPE_GCM;
        default:
            throw new \InvalidArgumentException('Unknown notification type ' . $str);
        }
    }
}
