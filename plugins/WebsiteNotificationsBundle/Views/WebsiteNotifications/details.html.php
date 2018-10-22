<?php

$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'website_notifications');
$view['slots']->set('headerTitle', $website_notification->getName());

$customButtons = [];

$translationContent = $view->render(
    'MauticCoreBundle:Translation:index.html.php',
    [
        'activeEntity' => $website_notification,
        'translations' => $translations,
        'model'        => 'website_notifications',
        'actionRoute'  => 'website_notifications_action',
        'nameGetter'   => 'getName',
    ]
);
$showTranslations = !empty(trim($translationContent));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'item'            => $website_notification,
            'customButtons'   => (isset($customButtons)) ? $customButtons : [],
            'templateButtons' => [
                'edit' => $view['security']->hasEntityAccess(
                    $permissions['website_notifications:website_notifications:editown'],
                    $permissions['website_notifications:website_notifications:editother'],
                    $website_notification->getCreatedBy()
                ),
                'delete' => $permissions['website_notifications:website_notifications:create'],
                'close'  => $view['security']->hasEntityAccess(
                    $permissions['website_notifications:website_notifications:viewown'],
                    $permissions['website_notifications:website_notifications:viewother'],
                    $website_notification->getCreatedBy()
                ),
            ],
            //'routeBase' => 'website_notifications',
            'actionRoute' => 'website_notifications_action',
            'indexRoute'  => 'website_notifications_index',
        ]
    )
);
$view['slots']->set(
    'publishStatus',
    $view->render('MauticCoreBundle:Helper:publishstatus_badge.html.php', ['entity' => $website_notification])
);
?>

<!-- start: box layout -->
<div class="box-layout">
    <!-- left section -->
    <div class="col-md-9 bg-white height-auto">
        <div class="bg-auto bg-dark-xs">
            <!-- notification detail header -->
            <div class="pr-md pl-md pt-lg pb-lg">
                <div class="box-layout">
                    <div class="col-xs-10">
                        <?php if ($website_notification->isTranslation(true)): ?>
                        <div class="small">
                            <a href="<?php echo $view['router']->path('mautic_notification_action', ['objectAction' => 'view', 'objectId' => $translations['parent']->getId()]); ?>" data-toggle="ajax">
                                <?php echo $view['translator']->trans('mautic.core.translation_of', ['%parent%' => $translations['parent']->getName()]); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!--/ notification detail header -->

            <!-- notification detail collapseable toggler -->
            <div class="hr-expand nm">
                <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.core.details'); ?>">
                    <a href="javascript:void(0)" class="arrow text-muted collapsed" data-toggle="collapse" data-target="#notification-details"><span class="caret"></span> <?php echo $view['translator']->trans('mautic.core.details'); ?></a>
                </span>
            </div>
            <!--/ notification detail collapseable toggler -->

            <!-- some stats -->
            <div class="pa-md">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel">
                            <div class="pt-0 pl-15 pb-10 pr-15">
			      <?php echo $view->render('WebsiteNotificationsBundle:WebsiteNotifications:preview.html.php', ['website_notification' => $website_notification]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ stats -->


            <!-- tabs controls -->
            <ul class="nav nav-tabs pr-md pl-md">
                <li class="active">
                    <a href="#contacts-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('mautic.lead.leads'); ?>
                    </a>
                </li>
                <?php if ($showTranslations): ?>
                    <li>
                        <a href="#translation-container" role="tab" data-toggle="tab">
                            <?php echo $view['translator']->trans('mautic.core.translations'); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <!--/ tabs controls -->
        </div>

        <!-- start: tab-content -->
        <div class="tab-content pa-md">
            <div class="tab-pane active bdr-w-0 page-list" id="contacts-container">
                <?php echo $contacts; ?>
            </div>
            <!-- #translation-container -->
            <?php if ($showTranslations): ?>
            <div class="tab-pane fade in bdr-w-0" id="translation-container">
                <?php echo $translationContent; ?>
            </div>
            <?php endif; ?>
            <!--/ #translation-container -->
        </div>
    </div>
    <!--/ left section -->

    <!-- right section -->
    <div class="col-md-3 bg-white bdr-l height-auto">
        <!-- activity feed -->
        <?php echo $view->render('MauticCoreBundle:Helper:recentactivity.html.php', ['logs' => $logs]); ?>
    </div>
    <!--/ right section -->
    <input name="entityId" id="entityId" type="hidden" value="<?php echo $view->escape($website_notification->getId()); ?>" />
</div>
<!--/ end: box layout -->
