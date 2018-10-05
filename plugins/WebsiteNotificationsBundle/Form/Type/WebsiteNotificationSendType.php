<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class WebsiteNotificationSendType.
 */
class WebsiteNotificationSendType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'website_notification',
            'website_notification_list',
            [
                'label'       => 'mautic.website_notifications.send.selectmessages',
                'label_attr'  => ['class' => 'control-label'],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.website_notifications.choosemessage.notblank']
                    ),
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'website_notification_send';
    }
}
