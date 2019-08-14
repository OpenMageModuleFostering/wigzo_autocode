<?php
class Wigzo_ServiceWorker_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

        $this->getResponse()->setHeader('Content-Type', 'text/javascript');

        $orgId = Mage::getStoreConfig('admin/wigzo/orgId');

        $enabled = true;
        $enabledFlag = Mage::getStoreConfig('admin/wigzo/enabled');
        if (NULL == $enabledFlag || $enabledFlag == "false") {
            $enabled = false;
        }

        if (! $enabled) {
            echo "/* Wigzo Extension is Disabled */";
            return;
        }

        echo <<<EOL

var wigzoConf = {
    "host": 'https://app.wigzo.com/',
    "orgtoken": "$orgId",
}
var showNotification = function (title, body, data) {
    data = data || {};
    var icon = data.icon || wigzoConf.host + "assets/img/notification_v1.png";
    var options = {
        body: body,
        //tag: "wigzo_2r22dg57q25",
        icon: icon,
        data: data,
    };
    return self.registration.showNotification(title, options);
}


self.addEventListener('install', function(event) {
    //Automatically take over the previous worker.
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function(event) {

});

self.addEventListener('push', function(event) {
    // Since there is no payload data with the first version
    // of push messages, we'll use some static content.
    // However you could grab some data from
    // an API and use it to populate a notification

    // In Chrome 44+ and other SW browsers, reg ID is part of endpoint, send the whole thing and let the server figure it out.
    event.waitUntil(self.registration.pushManager.getSubscription().then(function (registration) {
            var registrationId = null;
            if ('subscriptionId' in registration) {
                registrationId = registration.subscriptionId;
            } else {
                registrationId = registration.endpoint.split("/").reverse()[0];
            }

            return fetch(wigzoConf.host + "push/fetch-notification?orgtoken=" + wigzoConf.orgtoken + "&registrationId=" + registrationId).then(function(response) {
                return response.json().then(function(json) {
                    var promises = [];
                    for (var i = 0; i < json.notifications.length; i++) {
                        var notification = json.notifications[i];
                        promises.push(showNotification(notification.title, notification.body, notification.data));
                    }
                    return Promise.all(promises);
                });
            });

        })
    );
});


// The user has clicked on the notification ...
self.addEventListener('notificationclick', function(event) {
    // Android doesnâ€™t close the notification when you click on it
    // See: http://crbug.com/463146

    event.notification.close();
    if (event.notification.data && event.notification.data.url) {
        var url = event.notification.data && wigzoConf.host + event.notification.data.url;
    }

    // This looks to see if the current is already open and
    // focuses if it is
    event.waitUntil(clients.matchAll({
        type: "window"
    }).then(function(clientList) {
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];
            if (client.url == '/' && 'focus' in client)
                return client.focus();
        }
        if (clients.openWindow && url) {
            return clients.openWindow(url);
        }
    }));
});

EOL;

    }
}