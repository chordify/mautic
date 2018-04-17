<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\NotificationBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\NotificationBundle\Entity\Notification;
use Mautic\NotificationBundle\Entity\PushID;
use Mautic\NotificationBundle\Entity\Stat;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AppCallbackController extends CommonController
{
    public function indexAction(Request $request)
    {
        $requestBody = json_decode($request->getContent(), true);
        $em          = $this->get('doctrine.orm.entity_manager');
        $contactRepo = $em->getRepository(Lead::class);
        $pushIDRepo  = $em->getRepository(PushID::class);

        if(!array_key_exists('email', $requestBody) && !array_key_exists('push_id', $requestBody)) {
            throw new \InvalidArgumentException('At least email or push_id must be given');
        }

        /** @var Lead $contact */
        $contact = null;
        if(array_key_exists('email', $requestBody) && !empty($requestBody['email'])) {
            $contact = $contactRepo->findOneBy([
                'email' => $requestBody['email'],
            ]);
        }

        /** @var Lead $contactPushID */
        $pushID = null;
        $contactPushID = null;
        if(array_key_exists('push_id', $requestBody) && !empty($requestBody['push_id']) && array_key_exists('type', $requestBody)) {
            $pushID = $pushIDRepo->findOneBy([
                'type'   => PushID::typeFromString($requestBody['type']),
                'pushID' => $requestBody['push_id'],
            ]);
            if($pushID != null) {
                $contactPushID = $pushID->getLead();
            }
        }

        $pushIdCreated = false;

        // Check whether the push ID user and the e-mail user match. We want to
        // attach the push ID always to a single user, so merge in case we now
        // know new data.
        if($contact == null && $contactPushID == null) {
            $contact = new Lead();
            if(array_key_exists('email', $requestBody)) {
                $contact->setEmail($requestBody['email']);
            }
        } else if($contact == null && $contactPushID != null) {
            $contact = $contactPushID;
            if(array_key_exists('email', $requestBody)) {
                $contact->setEmail($requestBody['email']);
            }
        } else if($contact != null && $contactPushID != null) {
            if($contact->getId() != $contactPushID->getId()) {
                // Remove the push ID from the old contact
				$model = $this->getModel('lead.lead');
				$model->removePushIDFromLead($contactPushID, $pushID);
            }
        }

        // Always add the push id, also to update the 'enabled' status
        if(array_key_exists('push_id', $requestBody) && array_key_exists('type', $requestBody)) {
            $contact->addPushIDEntry(PushID::typeFromString($requestBody['type']), $requestBody['push_id'], $requestBody['enabled'], true);
            $pushIdCreated = true;
        }

        // We also allow setting the timezone and language through this endpoint
        if(array_key_exists('timezone', $requestBody)) {
            $contact->setTimezone($requestBody['timezone']);
        }
        if(array_key_exists('preferred_locale', $requestBody)) {
            $contact->addUpdatedField('preferred_locale', $requestBody['preferred_locale']);
        }

        $contact->setLastActive(new \DateTime());
        $contactRepo->saveEntity($contact);

        $statCreated = false;

        if (array_key_exists('stat', $requestBody)) {
            $stat             = $requestBody['stat'];
            $notificationRepo = $em->getRepository(Notification::class);
            $statRepo         = $em->getRepository(Stat::class);
            $statModel        = $this->getModel('notification');

            $statEntries      = $statModel->getNotificationStatByLeadId($stat['notification_id'], $contact->getId());

            if (count($statEntries) > 0) {
                $statCreated = true;
                $statEntry = $statEntries[0];

                $notificationRepo->upCount($stat['notification_id'], 'read');
                $statEntry->setDateRead(new \DateTime());
                $statRepo->saveEntity($statEntry);
            }
        }

        return new JsonResponse([
            'contact_id'       => $contact->getId(),
            'stat_recorded'    => $statCreated,
            'push_id_recorded' => $pushIdCreated ?: 'existing',
        ]);
    }

    public function deleteAction(Request $request)
    {
        $requestBody = json_decode($request->getContent(), true);
        $em          = $this->get('doctrine.orm.entity_manager');
        $pushIDRepo  = $em->getRepository(PushID::class);

        if(!array_key_exists('push_id', $requestBody)) {
            throw new \InvalidArgumentException('Missing field push_id');
        }
        if(!array_key_exists('type', $requestBody)) {
            throw new \InvalidArgumentException('Missing field type');
        }

        $pushID = $pushIDRepo->findOneBy([
            'type'   => PushID::typeFromString($requestBody['type']),
            'pushID' => $requestBody['push_id'],
        ]);
        if($pushID === null){
            return new JsonResponse([
                'contact_id'      => null,
                'push_id_deleted' => false,
            ]);
        }
        $contact   = $pushID->getLead();
        $contactId = $contact->getId();
		$model     = $this->getModel('lead.lead');
		$model->removePushIDFromLead($contact, $pushID);
        return new JsonResponse([
            'contact_id'      => $contactId,
            'push_id_deleted' => true
        ]);
    }

}
