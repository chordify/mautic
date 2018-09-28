<?php
$url    = $website_notification->getUrl();

?>
<label>Preview</label>
<div id="notification-preview" class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="text height-auto bg-white">
                <h4>
                    <?php 
                    if ($website_notification->getTitle()) {
                        echo $website_notification->getTitle();
                    } else {
                        echo 'Your notification title';
                    }
                    ?>  
                </h4>
                <p>
                    <?php 
                    if ($website_notification->getMessage()) {
                        echo $website_notification->getMessage();
                    } else {
                        echo 'The message body of your notification';
                    }?>  
                </p>
            </div>
        </div>
        <?php if ($url) : ?>
            <hr>
            <a href="<?php echo $url ?>"><?php echo $url ?></a>
        <?php endif; ?>
    </div>
</div>
