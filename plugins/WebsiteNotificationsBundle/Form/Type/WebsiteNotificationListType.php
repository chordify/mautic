<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\EntityLookupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteNotificationListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'modal_route'         => 'website_notifications_action',
                'modal_header'        => 'mautic.website_notifications.header.new',
                'model'               => 'website_notifications',
                'model_lookup_method' => 'getLookupResults',
                'lookup_arguments'    => function (Options $options) {
                    return [
                        'filter'  => '$data',
                        'limit'   => 0,
                        'start'   => 0,
                        'options' => [
                            'top_level'         => $options['top_level'],
                            'ignore_ids'        => $options['ignore_ids'],
                        ],
                    ];
                },
                'ajax_lookup_action' => function (Options $options) {
                    $query = [
                        'top_level'         => $options['top_level'],
                        'ignore_ids'        => $options['ignore_ids'],
                    ];

                    return 'website_notifications:getLookupChoiceList&'.http_build_query($query);
                },
                'expanded'          => false,
                'multiple'          => true,
                'required'          => false,
                'ignore_ids'        => [],
                'top_level'         => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'website_notification_list';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return EntityLookupType::class;
    }
}
