<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Executioner\ContactFinder\Limiter;

use Mautic\CampaignBundle\Executioner\Exception\NoContactsFoundException;

/**
 * Class ContactLimiter.
 */
class ContactLimiter
{
    /**
     * @var int|null
     */
    private $batchLimit;

    /**
     * @var int|null
     */
    private $contactId;

    /**
     * @var int|null
     */
    private $minContactId;

    /**
     * @var int|null
     */
    private $batchMinContactId;

    /**
     * @var int|null
     */
    private $maxContactId;

    /**
     * @var array
     */
    private $contactIdList;

    /**
     * @var int|null
     */
    private $threadId;

    /**
     * @var int|null
     */
    private $maxThreads;

    /**
     * @var int
     */
    private $totalDone;

    /**
     * @var int|null
     */
    private $totalLimit;

    /**
     * ContactLimiter constructor.
     *
     * @param int      $batchLimit
     * @param int|null $contactId
     * @param int|null $minContactId
     * @param int|null $maxContactId
     * @param array    $contactIdList
     * @param int|null $threadId
     * @param int|null $maxThreads
     * @param int|null $totalLimit
     */
    public function __construct(
        $batchLimit,
        $contactId = null,
        $minContactId = null,
        $maxContactId = null,
        array $contactIdList = [],
        $threadId = null,
        $maxThreads = null,
        $totalLimit = null
    ) {
        $this->batchLimit    = ($batchLimit) ? (int) $batchLimit : 100;
        $this->contactId     = ($contactId) ? (int) $contactId : null;
        $this->minContactId  = ($minContactId) ? (int) $minContactId : null;
        $this->maxContactId  = ($maxContactId) ? (int) $maxContactId : null;
        $this->contactIdList = $contactIdList;
        $this->totalDone     = 0;
        $this->totalLimit    = ($totalLimit) ? (int) $totalLimit : null;

        if ($threadId && $maxThreads) {
            $this->threadId     = (int) $threadId;
            $this->maxThreads   = (int) $maxThreads;

            if ($threadId > $maxThreads) {
                throw new \InvalidArgumentException('$threadId cannot be larger than $maxThreads');
            }
        }
    }

    /**
     * @return int
     */
    public function getBatchLimit()
    {
        if ($this->totalLimit) {
            return min($this->batchLimit, max($this->totalLimit - $this->totalDone, 0));
        }

        return $this->batchLimit;
    }

    /**
     * @return int|null
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @return int|null
     */
    public function getMinContactId()
    {
        return ($this->batchMinContactId) ? $this->batchMinContactId : $this->minContactId;
    }

    /**
     * @return int|null
     */
    public function getMaxContactId()
    {
        return $this->maxContactId;
    }

    /**
     * @return array
     */
    public function getContactIdList()
    {
        return $this->contactIdList;
    }

    /**
     * @param int $id
     *
     * @return $this
     *
     * @throws NoContactsFoundException
     */
    public function setBatchMinContactId($id)
    {
        // Prevent a never ending loop if the contact ID never changes due to being the last batch of contacts
        if ($this->minContactId && $this->minContactId > (int) $id) {
            throw new NoContactsFoundException();
        }

        // We've surpasssed the max so bai
        if ($this->maxContactId && $this->maxContactId < (int) $id) {
            throw new NoContactsFoundException();
        }

        // The same batch of contacts were somehow processed so let's stop to prevent the loop
        if ($this->batchMinContactId && $this->batchMinContactId >= $id) {
            throw new NoContactsFoundException();
        }

        $this->batchMinContactId = (int) $id;

        return $this;
    }

    /**
     * @return $this
     */
    public function resetBatchMinContactId()
    {
        $this->batchMinContactId =  null;
        $this->totalDone         = 0;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxThreads()
    {
        return $this->maxThreads;
    }

    /**
     * @return int|null
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * @param int $totalDone
     */
    public function setTotalDone($totalDone)
    {
        $this->totalDone = $totalDone;
    }

    /**
     * @return int
     */
    public function getTotalLimit()
    {
        return $this->totalLimit;
    }
}
