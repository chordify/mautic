<?php
if ($tmpl == 'index') {
    $view->extend('WebsiteNotificationsBundle:WebsiteNotifications:index.html.php');
}
?>

<?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
