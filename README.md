# GLPI mobile push notifications plugin

This plugin allows you to receive push notifications with the GLPI mobile app on android and ios devices. </br>
Android app: https://play.google.com/store/apps/details?id=net.intservices.glpi_flutter


## Installation
* Unpack archieve to your GLPI plugins folder ../glpi/plugins/glpimobilenotification. 
* Install and enable plugin in your GLPI web interface.


## Notification

**A new ticket**
* For admin(ID 3) and super-admin(ID 4) profiles for specified entity.
* For ticket technicians.
* For ticket watchers


**A ticket followup**
1. For admin(ID 3) and super-admin(ID 4) profiles for specified entity.
2. For ticket technicians, authors and watchers.
3. For ticket technicians for private comment


## Additional info
Plugin uses our firebase account (API key) by default. But you can register your own account and change api key in glpimobilenotification.cfg.
https://firebase.google.com/



If you have any questions contact us appsupport@int-services.net
