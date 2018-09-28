<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class WebsiteNotificationType.
 */
class WebsiteNotificationType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->em           = $factory->getEntityManager();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['content' => 'html', 'customHtml' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('website_notifications.website_notifications', $options));

        $builder->add(
            'name',
            'text',
            [
                'label'      => 'mautic.website_notifications.form.internal.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'description',
            'textarea',
            [
                'label'      => 'mautic.website_notifications.form.internal.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $builder->add(
            'title',
            'text',
            [
                'label'      => 'mautic.website_notifications.form.title',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'message',
            'textarea',
            [
                'label'      => 'mautic.website_notifications.form.message',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'rows'  => 6,
                ],
                'required' => true,
            ]
        );

        $builder->add(
            'url',
            'url',
            [
                'label'      => 'mautic.website_notifications.form.url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.notification.form.url.tooltip',
                ],
                'required' => false,
            ]
        );

        /*
        $builder->add(
            'image',
            'url',
            [
                'label'      => 'mautic.website_notifications.form.image',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.website_notifications.form.button.tooltip',
                ],
                'required' => false,
            ]
        );
        */

        $builder->add('isPublished', 'yesno_button_group');

        //add category
        $builder->add(
            'category',
            'category',
            [
                'bundle' => 'notification',
            ]
        );

        $builder->add(
            'language',
            'locale',
            [
                'label'      => 'mautic.core.language',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->em, 'WebsiteNotificationsBundle:WebsiteNotification');
        $builder->add(
            $builder->create(
                'translationParent',
                'hidden'
            )->addModelTransformer($transformer)
        );

        $translationParent = $options['data']->getTranslationParent();
        $builder->add(
            'realTranslationParent',
            'notification_list',
            [
                'label'      => 'mautic.core.form.translation_parent',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.core.form.translation_parent.help',
                ],
                'required'       => false,
                'multiple'       => false,
                'empty_value'    => 'mautic.core.form.translation_parent.empty',
                'mapped'         => false,
                'data'           => ($translationParent) ? $translationParent->getId() : null,
            ]
        );

        // After submit
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                $data['translationParent'] = isset($data['realTranslationParent']) ? $data['realTranslationParent'] : null;
                $event->setData($data);
            }
        );

        $builder->add('buttons', 'form_buttons');

        if (!empty($options['update_select'])) {
            $builder->add(
                'buttons',
                'form_buttons',
                [
                    'apply_text' => false,
                ]
            );
            $builder->add(
                'updateSelect',
                'hidden',
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        } else {
            $builder->add(
                'buttons',
                'form_buttons'
            );
        }

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MauticPlugin\WebsiteNotificationsBundle\Entity\WebsiteNotification',
            ]
        );

        $resolver->setOptional(['update_select']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'website_notification';
    }
}
