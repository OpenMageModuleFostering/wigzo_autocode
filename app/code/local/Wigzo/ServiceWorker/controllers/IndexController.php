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
            $this->getResponse()->setBody ("/* Wigzo Extension is Disabled */");
            return;
        }

        $serviceWorker = <<<EOL

var wigzoConf = {
    "host": 'https://app.wigzo.com/',
    "orgtoken": "$orgId",
}

var showNotification = function (title, body, data) {
    var data = data || {};
    var actionData=[];
    if(data.hasOwnProperty('actions')){
    //var actions= JSON.parse(data.actions);
    if(data.actions && data.actions.length!=0){
    var count = 0;
    data.actions.forEach(function(value,key){
    count = count+1;
    actionData.push({action : "action".concat(count.toString()), title: value.pushctaText })
    })
    }
    }
    var icon = data.icon || wigzoConf.host + "/assets/assets/img/notification_v1.png";
    var options = {
    body: body,
    //tag: "wigzo_2r22dg57q25",
    icon: icon,
    data: data,
    actions: actionData,
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
    // Android doesn’t close the notification when you click on it
    // See: http://crbug.com/463146

    event.notification.close();
    var url = "";
    if (event.notification.data && event.notification.data.url) {
    url = event.notification.data && wigzoConf.host + event.notification.data.url;
    }

    var action = event.notification.data.actions;
    if(action.length!=0){
    if(event.action == 'action1'){
    url = event.notification.data && wigzoConf.host + event.notification.data.actions[0].pushctaUrl
    return clients.openWindow(url);
    }
    else if(event.action == 'action2'){
    url = event.notification.data && wigzoConf.host + event.notification.data.actions[1].pushctaUrl
    return clients.openWindow(url);
    }
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

        $this->getResponse()->setBody ($serviceWorker);
    
    }
}
