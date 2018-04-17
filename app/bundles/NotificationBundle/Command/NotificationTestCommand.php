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
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $pushType  = $input->getOption('push-type');
        $pushToken = $input->getOption('push-id');

        $pushID = new PushID();
        $pushID->setType(PushID::typeFromString($pushType));
        $pushID->setPushID($pushToken);

		$notification = new Notification();
		$notification->setMobile(true);
		$notification->setHeading('Test');
		$notification->setMessage('This is a test');

		$factory           = $container->get('mautic.factory');
		$httpConnector     = $container->get('mautic.http.connector');
		$pageModelTrack    = $container->get('mautic.page.model.trackable');
        $integrationHelper = $container->get('mautic.helper.integration');

		$notifyApi = new AmazonSNSApi($factory, $httpConnector, $pageModelTrack, $integrationHelper);
		$res = $notifyApi->sendNotification([$pushID], $notification);
		print "Result: " . json_encode($res) . "\n";
	}
}