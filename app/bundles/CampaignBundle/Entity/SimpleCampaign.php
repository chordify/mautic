<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\FormBundle\Entity\Form;
use Mautic\LeadBundle\Entity\Lead as Contact;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\UserBundle\Entity\User;

/**
 * Class Campaign.
 */
class SimpleCampaign
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var null|\DateTime
     */
    private $publishUp;

    /**
     * @var null|\DateTime
     */
    private $publishDown;

    /**
     * @var ArrayCollection
     */
    private $events;

    /**
     * @var ArrayCollection
     */
    private $leads;

    /**
     * @var ArrayCollection
     */
    private $lists;

    /**
     * @var ArrayCollection
     */
    private $forms;

    /**
     * @var array
     */
    private $canvasSettings = [];

    /**
     * @var bool
     */
    private $allowRestart = false;

    /**
     * @var bool
     */
    private $isPublished = true;

    /**
     * @var null|\DateTime
     */
    private $dateAdded = null;

    /**
     * @var null|int
     */
    private $createdBy;

    /**
     * @var null|string
     */
    private $createdByUser;

    /**
     * @var null|\DateTime
     */
    private $dateModified;

    /**
     * var null|int.
     */
    private $modifiedBy;

    /**
     * @var null|string
     */
    private $modifiedByUser;

    /**
     * @var null|\DateTime
     */
    private $checkedOut;

    /**
     * @var null|int
     */
    private $checkedOutBy;

    /**
     * @var null|string
     */
    private $checkedOutByUser;

    /**
     * @var
     */
    public $deletedId;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->leads  = new ArrayCollection();
        $this->lists  = new ArrayCollection();
        $this->forms  = new ArrayCollection();
    }

    public function __clone()
    {
        $this->leads        = new ArrayCollection();
        $this->events       = new ArrayCollection();
        $this->lists        = new ArrayCollection();
        $this->forms        = new ArrayCollection();
        $this->id           = null;
        $this->dateAdded    = null;
        $this->dateModified = null;
        $this->checkedOut   = null;
        $this->isPublished  = false;

        parent::__clone();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Campaign
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Campaign
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Calls $this->addEvent on every item in the collection.
     *
     * @param array $events
     *
     * @return Campaign
     */
    public function addEvents(array $events)
    {
        foreach ($events as $id => $event) {
            $this->addEvent($id, $event);
        }

        return $this;
    }

    /**
     * Add events.
     *
     * @param                                     $key
     * @param \Mautic\CampaignBundle\Entity\Event $event
     *
     * @return Campaign
     */
    public function addEvent($key, Event $event)
    {
        $this->events[$key] = $event;

        return $this;
    }

    /**
     * Remove events.
     *
     * @param \Mautic\CampaignBundle\Entity\Event $event
     */
    public function removeEvent(\Mautic\CampaignBundle\Entity\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return ArrayCollection
     */
    public function getRootEvents()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'));
        $events   = $this->getEvents()->matching($criteria);

        // Doctrine loses the indexBy mapping definition when using matching so we have to manually reset them.
        // @see https://github.com/doctrine/doctrine2/issues/4693
        $keyedArrayCollection = new ArrayCollection();
        /** @var Event $event */
        foreach ($events as $event) {
            $keyedArrayCollection->set($event->getId(), $event);
        }

        unset($events);

        return $keyedArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getInactionBasedEvents()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('decisionPath', Event::PATH_INACTION));
        $events   = $this->getEvents()->matching($criteria);

        // Doctrine loses the indexBy mapping definition when using matching so we have to manually reset them.
        // @see https://github.com/doctrine/doctrine2/issues/4693
        $keyedArrayCollection = new ArrayCollection();
        /** @var Event $event */
        foreach ($events as $event) {
            $keyedArrayCollection->set($event->getId(), $event);
        }

        unset($events);

        return $keyedArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getEventsByType($type)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('eventType', $type));
        $events   = $this->getEvents()->matching($criteria);

        // Doctrine loses the indexBy mapping definition when using matching so we have to manually reset them.
        // @see https://github.com/doctrine/doctrine2/issues/4693
        $keyedArrayCollection = new ArrayCollection();
        /** @var Event $event */
        foreach ($events as $event) {
            $keyedArrayCollection->set($event->getId(), $event);
        }

        unset($events);

        return $keyedArrayCollection;
    }

    /**
     * Set publishUp.
     *
     * @param \DateTime $publishUp
     *
     * @return Campaign
     */
    public function setPublishUp($publishUp)
    {
        $this->publishUp = $publishUp;

        return $this;
    }

    /**
     * Get publishUp.
     *
     * @return \DateTime
     */
    public function getPublishUp()
    {
        return $this->publishUp;
    }

    /**
     * Set publishDown.
     *
     * @param \DateTime $publishDown
     *
     * @return Campaign
     */
    public function setPublishDown($publishDown)
    {
        $this->publishDown = $publishDown;

        return $this;
    }

    /**
     * Get publishDown.
     *
     * @return \DateTime
     */
    public function getPublishDown()
    {
        return $this->publishDown;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add lead.
     *
     * @param      $key
     * @param Lead $lead
     *
     * @return Campaign
     */
    public function addLead($key, Lead $lead)
    {
        $action     = ($this->leads->contains($lead)) ? 'updated' : 'added';
        $leadEntity = $lead->getLead();

        $this->leads[$key]                                     = $lead;

        return $this;
    }

    /**
     * Remove lead.
     *
     * @param Lead $lead
     */
    public function removeLead(Lead $lead)
    {
        $leadEntity                                              = $lead->getLead();
        $this->leads->removeElement($lead);
    }

    /**
     * Get leads.
     *
     * @return Lead[]|\Doctrine\Common\Collections\Collection
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * @return ArrayCollection
     */
    public function getLists()
    {
        return $this->lists;
    }

    /**
     * Add list.
     *
     * @param LeadList $list
     *
     * @return Campaign
     */
    public function addList(LeadList $list)
    {
        $this->lists[$list->getId()] = $list;

        $this->changes['lists']['added'][$list->getId()] = $list->getName();

        return $this;
    }

    /**
     * Remove list.
     *
     * @param LeadList $list
     */
    public function removeList(LeadList $list)
    {
        $this->lists->removeElement($list);
    }

    /**
     * @return ArrayCollection
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * Add form.
     *
     * @param Form $form
     *
     * @return Campaign
     */
    public function addForm(Form $form)
    {
        $this->forms[] = $form;

        return $this;
    }

    /**
     * Remove form.
     *
     * @param Form $form
     */
    public function removeForm(Form $form)
    {
        $this->forms->removeElement($form);
    }

    /**
     * @return mixed
     */
    public function getCanvasSettings()
    {
        return $this->canvasSettings;
    }

    /**
     * @param array $canvasSettings
     */
    public function setCanvasSettings(array $canvasSettings)
    {
        $this->canvasSettings = $canvasSettings;
    }

    /**
     * @return bool
     */
    public function getAllowRestart()
    {
        return $this->allowRestart;
    }

    /**
     * @return bool
     */
    public function allowRestart()
    {
        return $this->getAllowRestart();
    }

    /**
     * @param bool $allowRestart
     *
     * @return Campaign
     */
    public function setAllowRestart($allowRestart)
    {
        $this->allowRestart = $allowRestart;

        return $this;
    }

    /**
     * @return array
     */
    public function convertToArray()
    {
        return get_object_vars($this);
    }

    /**
     * Get contact membership.
     *
     * @param Contact $contact
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContactMembership(Contact $contact)
    {
        return $this->leads->matching(
            Criteria::create()
                    ->where(
                        Criteria::expr()->eq('lead', $contact)
                    )
                    ->orderBy(['dateAdded' => Criteria::DESC])
        );
    }

    /**
     * Check publish status with option to check against category, publish up and down dates.
     *
     * @param bool $checkPublishStatus
     * @param bool $checkCategoryStatus
     *
     * @return bool
     */
    public function isPublished($checkPublishStatus = true, $checkCategoryStatus = true)
    {
        if ($checkPublishStatus && method_exists($this, 'getPublishUp')) {
            $status = $this->getPublishStatus();
            if ($status == 'published') {
                //check to see if there is a category to check
                if ($checkCategoryStatus && method_exists($this, 'getCategory')) {
                    $category = $this->getCategory();
                    if ($category !== null && !$category->isPublished()) {
                        return false;
                    }
                }
            }

            return ($status == 'published') ? true : false;
        }

        return $this->getIsPublished();
    }

    /**
     * Set dateAdded.
     *
     * @param \DateTime $dateAdded
     *
     * @return $this
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded.
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set dateModified.
     *
     * @param \DateTime $dateModified
     *
     * @return $this
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified.
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set checkedOut.
     *
     * @param \DateTime $checkedOut
     *
     * @return $this
     */
    public function setCheckedOut($checkedOut)
    {
        $this->checkedOut = $checkedOut;

        return $this;
    }

    /**
     * Get checkedOut.
     *
     * @return \DateTime
     */
    public function getCheckedOut()
    {
        return $this->checkedOut;
    }

    /**
     * Set createdBy.
     *
     * @param User $createdBy
     *
     * @return $this
     */
    public function setCreatedBy($createdBy = null)
    {
        if ($createdBy != null && !$createdBy instanceof User) {
            $this->createdBy = $createdBy;
        } else {
            $this->createdBy = ($createdBy != null) ? $createdBy->getId() : null;
            if ($createdBy != null) {
                $this->createdByUser = $createdBy->getName();
            }
        }

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set modifiedBy.
     *
     * @param User $modifiedBy
     *
     * @return mixed
     */
    public function setModifiedBy($modifiedBy = null)
    {
        if ($modifiedBy != null && !$modifiedBy instanceof User) {
            $this->modifiedBy = $modifiedBy;
        } else {
            $this->modifiedBy = ($modifiedBy != null) ? $modifiedBy->getId() : null;

            if ($modifiedBy != null) {
                $this->modifiedByUser = $modifiedBy->getName();
            }
        }

        return $this;
    }

    /**
     * Get modifiedBy.
     *
     * @return User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set checkedOutBy.
     *
     * @param User $checkedOutBy
     *
     * @return mixed
     */
    public function setCheckedOutBy($checkedOutBy = null)
    {
        if ($checkedOutBy != null && !$checkedOutBy instanceof User) {
            $this->checkedOutBy = $checkedOutBy;
        } else {
            $this->checkedOutBy = ($checkedOutBy != null) ? $checkedOutBy->getId() : null;

            if ($checkedOutBy != null) {
                $this->checkedOutByUser = $checkedOutBy->getName();
            }
        }

        return $this;
    }

    /**
     * Get checkedOutBy.
     *
     * @return User
     */
    public function getCheckedOutBy()
    {
        return $this->checkedOutBy;
    }

    /**
     * Set isPublished.
     *
     * @param bool $isPublished
     *
     * @return $this
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get isPublished.
     *
     * @return bool
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Check the publish status of an entity based on publish up and down datetimes.
     *
     * @return string early|expired|published|unpublished
     *
     * @throws \BadMethodCallException
     */
    public function getPublishStatus()
    {
        $dt      = new DateTimeHelper();
        $current = $dt->getLocalDateTime();
        if (!$this->isPublished(false)) {
            return 'unpublished';
        }

        $status = 'published';
        if (method_exists($this, 'getPublishUp')) {
            $up = $this->getPublishUp();
            if (!empty($up) && $current < $up) {
                $status = 'pending';
            }
        }
        if (method_exists($this, 'getPublishDown')) {
            $down = $this->getPublishDown();
            if (!empty($down) && $current >= $down) {
                $status = 'expired';
            }
        }

        return $status;
    }

    /**
     * @return string
     */
    public function getCheckedOutByUser()
    {
        return $this->checkedOutByUser;
    }

    /**
     * @return string
     */
    public function getCreatedByUser()
    {
        return $this->createdByUser;
    }

    /**
     * @return string
     */
    public function getModifiedByUser()
    {
        return $this->modifiedByUser;
    }

    /**
     * @param mixed $createdByUser
     *
     * @return $this
     */
    public function setCreatedByUser($createdByUser)
    {
        $this->createdByUser = $createdByUser;

        return $this;
    }

    /**
     * @param mixed $modifiedByUser
     *
     * @return $this
     */
    public function setModifiedByUser($modifiedByUser)
    {
        $this->modifiedByUser = $modifiedByUser;

        return $this;
    }

    /**
     * @param mixed $checkedOutByUser
     *
     * @return $this
     */
    public function setCheckedOutByUser($checkedOutByUser)
    {
        $this->checkedOutByUser = $checkedOutByUser;

        return $this;
    }
}
