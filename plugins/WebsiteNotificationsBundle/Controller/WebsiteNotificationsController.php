<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\LeadBundle\Controller\EntityContactsTrait;

class WebsiteNotificationsController extends FormController
{
    use EntityContactsTrait;

    public function indexAction($page = 1)
    {
        $model = $this->getModel('website_notifications');

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'website_notifications:website_notifications:viewown',
                'website_notifications:website_notifications:viewother',
                'website_notifications:website_notifications:create',
                'website_notifications:website_notifications:editown',
                'website_notifications:website_notifications:editother',
                'website_notifications:website_notifications:deleteown',
                'website_notifications:website_notifications:deleteother',
                'website_notifications:website_notifications:publishown',
                'website_notifications:website_notifications:publishother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['website_notifications:website_notifications:viewown'] && !$permissions['website_notifications:website_notifications:viewother']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        $session = $this->get('session');

        //set limits
        $limit = $session->get('mautic.website_notifications.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.website_notifications.filter', ''));
        $session->set('mautic.website_notifications.filter', $search);

        $filter = [
            'string' => $search,
        ];

        if (!$permissions['website_notifications:website_notifications:viewother']) {
            $filter['force'][] =
                               ['column' => 'e.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

        //do not list variants in the main list
        $translator        = $this->get('translator');
        $langSearchCommand = $translator->trans('mautic.core.searchcommand.lang');
        if (strpos($search, "{$langSearchCommand}:") === false) {
            $filter['force'][] = ['column' => 'e.translationParent', 'expr' => 'isNull'];
        }

        $orderBy    = $session->get('mautic.website_notifications.orderby', 'e.name');
        $orderByDir = $session->get('mautic.website_notifications.orderbydir', 'DESC');

        $notifications = $model->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]
        );

        $count = count($notifications);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (floor($count / $limit)) ?: 1;
            }

            $session->set('mautic.notification.page', $lastPage);
            $returnUrl = $this->generateUrl('mautic_notification_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MauticNotificationBundle:Notification:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_notification_index',
                        'mauticContent' => 'notification',
                    ],
                ]
            );
        }
        $session->set('mautic.website_notifications.page', $page);

        // Return the view
        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue' => $search,
                    'items'       => $notifications,
                    'totalItems'  => $count,
                    'page'        => $page,
                    'limit'       => $limit,
                    'tmpl'        => $this->request->get('tmpl', 'index'),
                    'permissions' => $permissions,
                    'model'       => $model,
                    'security'    => $this->get('mautic.security'),
                ],
                'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#website_notifications_index',
                    'mauticContent' => 'website_notifications',
                    'route'         => $this->generateUrl('website_notifications_index', ['page' => $page]),
                ],
            ]
        );
    }

    public function newAction($entity = null)
    {
        $model = $this->getModel('website_notifications');

        if (!$entity instanceof WebsiteNotification) {
            $entity = $model->getEntity();
        }

        $method  = $this->request->getMethod();
        $session = $this->get('session');

        if (!$this->get('mautic.security')->isGranted('website_notifications:website_notifications:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page   = $session->get('mautic.notification.page', 1);
        $action = $this->generateUrl('website_notifications_action', ['objectAction' => 'new']);

        $updateSelect = ($method == 'POST')
                      ? $this->request->request->get('website_notifications[updateSelect]', false, true)
                      : $this->request->get('updateSelect', false);

        //create the form
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect]);

        ///Check for a submitted form and process it
        if ($method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'website_notifications_index',
                            '%url%'       => $this->generateUrl(
                                'website_notifications_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = [
                            'objectAction' => 'view',
                            'objectId'     => $entity->getId(),
                        ];
                        $returnUrl = $this->generateUrl('website_notifications_action', $viewParameters);
                        $template  = 'WebsiteNotificationsBundle:WebsiteNotifications:view';
                    } else {
                        //return edit view so that all the session stuff is loaded
                        return $this->editAction($entity->getId(), true);
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('website_notifications_index', $viewParameters);
                $template       = 'WebsiteNotificationsBundle:WebsiteNotifications:index';
                //clear any modified content
                $session->remove('mautic.website_notification.'.$entity->getId().'.content');
            }

            $passthrough = [
                'activeLink'    => 'website_notifications_index',
                'mauticContent' => 'website_notifications',
            ];

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => $passthrough,
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'                 => $this->setFormTheme($form, 'WebsiteNotificationsBundle:WebsiteNotifications:form.html.php', 'WebsiteNotificationsBundle:FormTheme\WebsiteNotification'),
                    'website_notification' => $entity,
                ],
                'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#website_notifications_index',
                    'mauticContent' => 'website_notifications',
                    'updateSelect'  => InputHelper::clean($this->request->query->get('updateSelect')),
                    'route'         => $this->generateUrl(
                        'website_notifications_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    public function editAction($objectId, $ignorePost = false, $forceTypeSelection = false)
    {
        $model   = $this->getModel('website_notifications');
        $method  = $this->request->getMethod();
        $entity  = $model->getEntity($objectId);
        $session = $this->get('session');
        $page    = $session->get('mautic.website_notifications.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('website_notifications_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'WebsiteNotificationBundle:WebsiteNotifications:index',
            'passthroughVars' => [
                'activeLink'    => 'website_notifications_index',
                'mauticContent' => 'website_notifications',
            ],
        ];

        //not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mautic.website_notifications.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'website_notifications:website_notifications:viewown',
            'website_notifications:website_notifications:viewother',
            $entity->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'website_notifications');
        }

        //Create the form
        $action = $this->generateUrl('website_notifications_action', ['objectAction' => 'edit', 'objectId' => $objectId]);

        $updateSelect = ($method == 'POST')
                      ? $this->request->request->get('website_notification[updateSelect]', false, true)
                      : $this->request->get('updateSelect', false);

        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect]);

        ///Check for a submitted form and process it
        if (!$ignorePost && $method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash(
                        'mautic.core.notice.updated',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'website_notifications_index',
                            '%url%'       => $this->generateUrl(
                                'website_notifications_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ],
                        'warning'
                    );
                }
            } else {
                //clear any modified content
                $session->remove('mautic.website_notification.'.$objectId.'.content');
                //unlock the entity
                $model->unlockEntity($entity);
            }

            $template    = 'WebsiteNotificationsBundle:WebsiteNotifications:view';
            $passthrough = [
                'activeLink'    => 'website_notifications_index',
                'mauticContent' => 'website_notifications',
            ];

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('website_notifications_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                            'passthroughVars' => $passthrough,
                        ]
                    )
                );
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'                 => $this->setFormTheme($form, 'WebsiteNotificationsBundle:WebsiteNotification:form.html.php'),
                    'website_notification' => $entity,
                    'forceTypeSelection'   => $forceTypeSelection,
                ],
                'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#website_notifications_index',
                    'mauticContent' => 'website_notifications',
                    'updateSelect'  => InputHelper::clean($this->request->query->get('updateSelect')),
                    'route'         => $this->generateUrl(
                        'website_notifications_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    public function previewAction($objectId)
    {
        $model        = $this->getModel('website_notifications');
        $notification = $model->getEntity($objectId);

        if ($notification != null
            && $this->get('mautic.security')->hasEntityAccess(
                'website_notifications:website_notifications:editown',
                'website_notifications:website_notifications:editother'
            )
        ) {
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'website_notification' => $notification,
                ],
                'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:preview.html.php',
            ]
        );
    }

    public function viewAction($objectId)
    {
        $model    = $this->getModel('website_notifications');
        $security = $this->get('mautic.security');

        $notification = $model->getEntity($objectId);

        //set the page we came from
        $page = $this->get('session')->get('mautic.website_notification.page', 1);

        if ($notification === null) {
            //set the return URL
            $returnUrl = $this->generateUrl('website_notifications_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:index',
                    'passthroughVars' => [
                        'activeLink'    => '#website_notifications_index',
                        'mauticContent' => 'website_notifications',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.website_notifications.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'website_notifications:website_notifications:viewown',
            'website_notifications:website_notifications:viewother',
            $notification->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        // Audit Log
        $logs = $this->getModel('core.auditLog')->getLogForObject('website_notification', $notification->getId(), $notification->getDateAdded());

        //get related translations
        list($translationParent, $translationChildren) = $notification->getTranslations();

        return $this->delegateView([
            'returnUrl'      => $this->generateUrl('website_notifications_action', ['objectAction' => 'view', 'objectId' => $notification->getId()]),
            'viewParameters' => [
                'website_notification' => $notification,
                'logs'                 => $logs,
                'translations'         => [
                    'parent'   => $translationParent,
                    'children' => $translationChildren,
                ],
                'permissions'  => $security->isGranted([
                    'website_notifications:website_notifications:viewown',
                    'website_notifications:website_notifications:viewother',
                    'website_notifications:website_notifications:create',
                    'website_notifications:website_notifications:editown',
                    'website_notifications:website_notifications:editother',
                    'website_notifications:website_notifications:deleteown',
                    'website_notifications:website_notifications:deleteother',
                    'website_notifications:website_notifications:publishown',
                    'website_notifications:website_notifications:publishother',
                ], 'RETURN_ARRAY'),
                'security'    => $security,
                'contacts'    => $this->forward(
                    'WebsiteNotificationsBundle:WebsiteNotifications:contacts',
                    [
                        'objectId'   => $notification->getId(),
                        'page'       => $this->get('session')->get('mautic.website_notifications.contact.page', 1),
                        'ignoreAjax' => true,
                    ]
                )->getContent(),
            ],
            'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:details.html.php',
            'passthroughVars' => [
                'activeLink'    => '#website_notifications_index',
                'mauticContent' => 'website_notifications',
            ],
        ]);
    }

    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('mautic.website_notifications.page', 1);
        $returnUrl = $this->generateUrl('website_notifications_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'WebsiteNotificationsBundle:WebsiteNotifications:index',
            'passthroughVars' => [
                'activeLink'    => 'website_notifications_index',
                'mauticContent' => 'website_notifications',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('website_notifications');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.website_notifications.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'website_notifications:website_notifications:deleteown',
                'website_notifications:website_notifications:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'website_notification');
            }

            $model->deleteEntity($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $entity->getName(),
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * @param     $objectId
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function contactsAction($objectId, $page = 1)
    {
        return $this->generateContactsGrid(
            $objectId,
            $page,
            'website_notifications:website_notifications:view',
            'website_notifications',
            'website_notifications_inbox',
            null,
            'notification_id',
            [],
            [],
            'contact_id'
        );
    }
}
