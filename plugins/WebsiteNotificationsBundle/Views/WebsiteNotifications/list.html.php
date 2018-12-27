<?php
if ($tmpl == 'index') {
    $view->extend('WebsiteNotificationsBundle:WebsiteNotifications:index.html.php');
}

echo $view['assets']->includeScript('plugins/WebsiteNotificationsBundle/Assets/js/websiteNotifications.js', 'websiteNotificationsLoadStats', 'websiteNotificationsLoadStats');

if (count($items)):

    ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered website-notifications-list">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'actionRoute'     => 'website_notifications_action',
                        'templateButtons' => [
                            'delete' => $permissions['website_notifications:website_notifications:deleteown']
                                || $permissions['website_notifications:website_notifications:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'notification',
                        'orderBy'    => 'e.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-notification-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'notification',
                        'orderBy'    => 'c.title',
                        'text'       => 'mautic.core.category',
                        'class'      => 'visible-md visible-lg col-notification-category',
                    ]
                );
                ?>

                <th class="visible-sm visible-md visible-lg col-website-notifications-stats"><?php echo $view['translator']->trans('mautic.core.stats'); ?></th>

                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'website_notifications',
                        'orderBy'    => 'e.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'visible-md visible-lg col-notification-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($items as $item):
                ?>
                <tr>
                    <td>
                        <?php
                        $edit = $view['security']->hasEntityAccess(
                            $permissions['website_notifications:website_notifications:editown'],
                            $permissions['website_notifications:website_notifications:editother'],
                            $item->getCreatedBy()
                        );
                        $customButtons = [
                            [
                                'attr' => [
                                    'data-toggle' => 'ajaxmodal',
                                    'data-target' => '#MauticSharedModal',
                                    'data-header' => $view['translator']->trans('mautic.website_notifications.website_notifications.header.preview'),
                                    'data-footer' => 'false',
                                    'href'        => $view['router']->path(
                                        'website_notifications_action',
                                        ['objectId' => $item->getId(), 'objectAction' => 'preview']
                                    ),
                                ],
                                'btnText'   => $view['translator']->trans('mautic.website_notifications.preview'),
                                'iconClass' => 'fa fa-share',
                            ],
                        ];
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $edit,
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['website_notifications:website_notifications:deleteown'],
                                        $permissions['website_notifications:website_notifications:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'actionRoute'   => 'website_notifications_action',
                                'customButtons' => $customButtons,
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                                <?php echo $view->render(
                                    'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                                    ['item' => $item, 'model' => 'website_notifications']
                                ); ?>
                            <a href="<?php echo $view['router']->path(
                                'website_notifications_action',
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>">
                                <?php echo $item->getName(); ?>
                                <?php
                                $hasTranslations    = $item->isTranslation();

                                if ($hasTranslations): ?>
                                        <span data-toggle="tooltip" title="<?php echo $view['translator']->trans(
                                            'mautic.core.icon_tooltip.translation'
                                        ); ?>">
                                            <i class="fa fa-fw fa-language"></i>
                                        </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                        <span class="mt-xs label label-warning"
                              id="sent-count-<?php echo $item->getId(); ?>">
                                <div style="width: 50px;">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                        </span>
                        <span class="mt-xs label label-success"
                              id="read-count-<?php echo $item->getId(); ?>">
                                <div style="width: 50px;">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                        </span>
                        <span class="mt-xs label label-primary"
                              id="read-percent-<?php echo $item->getId(); ?>">
                                <div style="width: 50px;">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                        </span>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php echo $view->render(
            'MauticCoreBundle:Helper:pagination.html.php',
            [
                'totalItems' => $totalItems,
                'page'       => $page,
                'limit'      => $limit,
                'baseUrl'    => $view['router']->path('mautic_notification_index'),
                'sessionVar' => 'notification',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
