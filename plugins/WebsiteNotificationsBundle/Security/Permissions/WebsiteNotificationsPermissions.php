<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Security\Permissions;

use Mautic\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

class WebsiteNotificationsPermissions extends AbstractPermissions
{
    /**
     * {@inheritdoc}
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->addStandardPermissions('categories');
        $this->addExtendedPermissions('website_notifications');
    }

    /**
     * {@inheritdoc}
     *
     * @return string|void
     */
    public function getName()
    {
        return 'website_notifications';
    }

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface &$builder, array $options, array $data)
    {
        $this->addStandardFormFields('website_notifications', 'categories', $builder, $data);
        $this->addExtendedFormFields('website_notifications', 'website_notifications', $builder, $data);
    }
}
