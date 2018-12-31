/** WebsiteNotificationsBundle **/
Mautic.websiteNotificationsLoadStats = function (container, response) {
    if (mQuery('table.website-notifications-list').length) {
        mQuery('td.col-stats').each(function () {
            var id = mQuery(this).attr('data-stats');
            // Process the request one at a time or the xhr will cancel the previous
            Mautic.ajaxActionRequest(
                'plugin:websiteNotifications:getWebsiteNotificationCountStats',
                {id: id},
                function (response) {
                    if (response.success && mQuery('#sent-count-' + id + ' div').length) {
                        mQuery('#sent-count-' + id + ' > div').html(response.sentCount);
                        mQuery('#read-count-' + id + ' > div').html(response.readCount);
                        mQuery('#read-percent-' + id + ' > div').html(response.readPercent);
                    }
                },
                false,
                true
            );

        });
    }
}
