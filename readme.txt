=== Gigya Socialize for WordPress ===
Contributors: nickohrn
Tags: login, admin, user, accounts, authentication
Requires at least: 2.6
Tested up to: 2.8
Stable tag: 1.0.0

Provide your visitors with the option of logging in or connecting to your blog through their social networking accounts.

== Description ==

Gigya Socialize is a powerful cross social network platform that allows for authentication via a number of different providers including, but not limited to,
Facebook, Twitter, and MySpace.  With the Gigya Socialize for WordPress plugin, you can allow your users to authenticate via any one of the supported providers.

To enable this in older installations, this plugin uses a roundabout way of connecting users via their social network.  The first time a user logs in to your site
using the Gigya Socialize login component, the site creates a WordPress user in the database, connects the social network account to the user, and then automatically 
logs the user in.  When a user returns and logs in again, the created user is retrieved and all correct log in procedures are done again.

The Gigya Socialize plugin uses proprietary authentication techniques to prevent account spoofing from happening.  If your users do not have JavaScript enabled, they will
not benefit from this plugin.

== Installation ==

1. Upload the `gigya-socialize-for-wordpress` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure your API information in the Gigya Socialize settings menu
