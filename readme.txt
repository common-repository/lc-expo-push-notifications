=== LC Expo Push Notifications ===
Contributors: LCweb Projects
Donate link: http://www.lcweb.it/donations
Tags: react, react native, expo, push notifications, mobile app, application, device
Requires at least: 3.8
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Expo (React Native) push notifications made easy for WordPress users

== Description ==

Have you got an Expo (react native) application and want to implement push notifications?
This plugin is the easiest way to get the job done in a proper way.

Notifications scheduling, emoji support and extra parameters builder. It's all you need to get started in minutes!
Let users subscribe to notifications simply calling an endpoint URL in your app.

You can also integrate your Google Analytics account: the plugin will log events whenever users register (or deregister). 
Super useful when it comes to know how users database changes depending on advertising or social campaigns.

Please refer to the [Expo documentation](https://docs.expo.io/push-notifications/overview/) on how to get users token.


= NOTE: = No support provided



== Installation & Use ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress. A new menu element will be created 
3. Check settings page to know endpoint URLs, to manage additional parameters and to enable Google Analytics integration
4. Create notification categories or directly new notifications
5. Integrate the register endpoint in your app (just call the URL)
6. Create a cronjob calling the related endpoint (or open the URL to manually trigger notifications). Please note only one notification is sent per time, to not bug end users.
 

== Screenshots ==

1. Settings
2. Notification page

== Changelog ==

= 1.0 =
Initial release

== Upgrade notice ==

none