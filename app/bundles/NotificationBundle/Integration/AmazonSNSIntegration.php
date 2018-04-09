<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\NotificationBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;

/**
 * Class AmazonSNSIntegration.
 */
class AmazonSNSIntegration extends AbstractIntegration
{
    /**
     * @var bool
     */
    protected $coreIntegration = true;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'AmazonSNS';
    }

    public function getIcon()
    {
        return 'app/bundles/NotificationBundle/Assets/img/AmazonSNS.png';
    }

    public function getSupportedFeatures()
    {
        return [
            'mobile',
            'landing_page_enabled',
        ];
    }

    public function getSupportedFeatureTooltips()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
			'key'             => 'mautic.notification.config.form.notification.amazon_key',
			'key_secret'      => 'mautic.notification.config.form.notification.amazon_key_secret',
            'region'          => 'mautic.notification.config.form.notification.region',
        ];
    }

    /**
     * @return array
     */
    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea == 'features') {
            /* @var FormBuilder $builder */
            $builder->add(
                'apple_dev_arn',
                TextType::class,
                [
                    'label'    => 'mautic.notification.config.form.notification.application_arn_apple_dev',
                    'required' => false,
                    'attr'     => [
                        'class' => 'form-control',
                    ],
                ]
            );
            $builder->add(
                'apple_live_arn',
                TextType::class,
                [
                    'label'    => 'mautic.notification.config.form.notification.application_arn_apple_live',
                    'required' => false,
                    'attr'     => [
                        'class' => 'form-control',
                    ],
                ]
            );
            $builder->add(
                'gcm_arn',
                TextType::class,
                [
                    'label'    => 'mautic.notification.config.form.notification.application_arn_gcm',
                    'required' => false,
                    'attr'     => [
                        'class' => 'form-control',
                    ],
                ]
            );
		}
    }
}
