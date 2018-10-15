<?php

$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'website_notifications');

$header = ($website_notification->getId()) ?
    $view['translator']->trans('mautic.website_notifications.header.edit',
        ['%name%' => $website_notification->getName()]) :
    $view['translator']->trans('mautic.website_notifications.header.new');

$view['slots']->set('headerTitle', $header);

?>

<?php echo $view['form']->start($form); ?>
<div class="box-layout">
    <div class="col-md-9 height-auto bg-white">
        <div class="row">
            <div class="col-xs-12">
                <!-- tabs controls -->
                <!--/ tabs controls -->
                <div class="tab-content pa-md">
                    <div class="tab-pane fade in active bdr-w-0" id="notification-container">
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['name']); ?>
                                <?php echo $view['form']->row($form['title']); ?>
                                <?php echo $view['form']->row($form['message']); ?>
                                <?php echo $view['form']->row($form['url']); ?>
                                <?php echo $view['form']->row($form['image']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 bg-white height-auto bdr-l">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php echo $view['form']->row($form['category']); ?>
            <?php echo $view['form']->row($form['language']); ?>
            <?php echo $view['form']->row($form['realTranslationParent']); ?>
            <hr />
            <div class="hide">
                <?php echo $view['form']->row($form['isPublished']); ?>
                <?php echo $view['form']->rest($form); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>
