<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\NotificationBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Mautic\NotificationBundle\Api\AmazonSNSApi;
use Mautic\NotificationBundle\Entity\Notification;
use Mautic\NotificationBundle\Entity\PushID;

/**
 * Class NotificationTestCommand.
 */
class NotificationTestCommand extends ModeratedCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mautic:notification:test')
            ->setDescription('Send a test notification.')
            ->addOption(
                '--push-type',
                null,
                InputOption::VALUE_REQUIRED,
                'The type of this push id.'
            )
            ->addOption(
                '--push-id',
                null,
                InputOption::VALUE_REQUIRED,
                'The push id of this user.'
            )
            ->addOption(
                '--lead-id',
                null,
                InputOption::VALUE_REQUIRED,
                'The id of the lead to send the notification to, or use type & id.'
            )
            ->addOption(
                '--notification-id',
                null,
                InputOption::VALUE_REQUIRED,
                'The id of the notification.'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $factory           = $container->get('mautic.factory');
        $httpConnector     = $container->get('mautic.http.connector');
        $pageModelTrack    = $container->get('mautic.page.model.trackable');
        $integrationHelper = $container->get('mautic.helper.integration');

        $notificationModel = $factory->getModel('notification');
        $leadModel         = $factory->getModel('lead');

        $pushType       = $input->getOption('push-type');
        $pushToken      = $input->getOption('push-id');
        $notificationID = $input->getOption('notification-id');
        $leadID         = $input->getOption('lead-id');

        $lead = null;
        if($leadID != null) {
            $lead = $leadModel->getEntity($leadID);
            if($lead == null) {
                throw new \Exception("Lead " . $leadID . " not found");
            }
        }

        if($lead != null) {
            $pushIDs = $lead->getPushIDs();
            if(count($pushIDs) == 0) {
                throw new \Exception("Lead has no push ids");
            }
            $pushID = $pushIDs[0];
        } else {
            $pushID = new PushID();
            $pushID->setType(PushID::typeFromString($pushType));
            $pushID->setPushID($pushToken);
        }

        $notification = $notificationModel->getEntity($notificationID);
        if($notification == null) {
            throw new \Exception("Notification " . $notificationID . " not found");
        }

        // Use a translation if available
        if($lead != null) {
            list($ignore, $notificationTranslation) = $notificationModel->getTranslatedEntity($notification, $lead);
            if( $notificationTranslation !== null ){
                $notification = $notificationTranslation;
            }
        }

        $notifyApi = new AmazonSNSApi($factory, $httpConnector, $pageModelTrack, $integrationHelper);
        $res = $notifyApi->sendNotification([$pushID], $notification);
        print "Result: " . json_encode($res) . "\n";
    }
}