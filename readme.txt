=== Gigya - Social Infrastructure  ===

Contributors: gigya.com, konforti, luciodiri
Tags: CIAM, CIM, Registration, Social Login, Oauth, OpenSocial, Graph API, Facebook Connect, Linkedin, Twitter, authentication, OpenID,  newsfeed, tweet, status update, registration, social APIs, sharing, plugin, social bookmark, social network, Facebook, community, comments, reactions, game mechanics, register, Gigya, Social Infrastructure, feed
Requires at least: 4.2
Tested up to: 4.9
Stable tag: 5.8.1
License: Apache v2.0

Integrate your WordPress site with Gigya

== Description ==
Gigya's Customer Identity Management Platform helps companies build better customer relationships by turning unknown visitors into known, loyal and engaged customers. With Gigya’s technology, businesses increase registrations and identify customers across devices, consolidate data into rich customer profiles, and provide better service, products and experiences by integrating data into marketing and service applications.

More than 700 of the world’s leading businesses such as Fox, Forbes, and Verizon rely on Gigya to build identity-driven relationships and to provide scalable, secure Customer Identity Management.

If you don't have an account yet, <a href="https://console.gigya.com/register.aspx" title="sign up">sign up for a free trial</a>.

**Add the Following Features to Your WordPress blog or website:**

**Consumer Identity Management products**

* Social Login - Allow users to quickly sign up to your blog or website using a preferred social network account.
* Registration-as-a-Service (RaaS) - Build, deploy and manage registration forms and profile management flows across your WordPress blog or Website.

**Social Plugins**

* Share Bar - Allow your site visitors to easily share posts with their social network friends.
* Comments - Enable your site visitors to post comments and have discussions about published content on your site.
* Rating & Reviews - Give your customers an easy way to provide feedback on products and content across your site and share that feedback with friends in their social networks.
* Gamification - Motivate your users to take valuable actions by offering rewards such as higher status, special offers, badges, points and more.
* Reactions - Make it easy for users to react to content on your site and share their reactions with friends on social networks.


For more information, installation steps and configuration options - please refer to Gigya's documentation:
<a href="http://developers.gigya.com/display/GD/WordPress+Plugin" title="Installation and Configuration guide">Gigya's Wordpress plugin - Installation and Configuration guide</a>


== Installation ==

You can read more about the installation steps and other advanced configuration options in Gigya's online documentation <a href="https://developers.gigya.com/display/GD/WordPress+Plugin" title="Installation and Configuration guide">here</a>

1.	After downloading the Gigya plug-in, unpack and upload the folder to the the /wp-content/plugins/ directory on your blog.
2.	Go to the Plug-in tab in the WordPress administration panel, find the Gigya plug-in on the list and click Activate.
3.	Proceed to the plug-in settings page ("Gigya" item on the left sidebar) to configure your plug-in. The plug-in needs your API information in the Gigya settings menu, so please grab your API key, and secret code from the <a href="https://console.gigya.com/" title="Gigya">Gigya's website</a>.

For question about installations or configuration, please contact your account manager or contact our support via the support page on the Gigya site.

== Screenshots ==

1. WordPress Registration page with Gigya's Social Login
2. Registration-as-a-Service pop-up screen
3. Gigya's administration panel
4. Gigya widgets
5. Share with your social graph & comment on posts and articles.



== Changelog ==

= 2.0.5 =

* Support WordPress networks
* New customized Share bar including Facebook like button and Google+1.
* Bug fixes

= 2.0.6 =

* Gigya's Comments plugin
* Bug fixes

= 3.0 =
* Option to choose the position of the share bar plugin.
* Improve the integration of Gigya comments with the WordPress comments.
* Call Gigya notifyLogin for user logging in with site credentials for better integration with Gigya plugins.
* Option to select which JQuery version to be used by gigya plugin.
* Support Gigya Login widget as a shortcode - enables site owners to embed the Login widget anywhere on the site template without a sidebar.
* Bug fixes

= 3.0.5 =
* Security update
* Bug fixes


= 4.0 =
* Gigya's <a href="https://developers.gigya.com/display/GD/Reactions" title="Reactions">Reactions bar</a>
* Gigya's <a href="https://developers.gigya.com/display/GD/Loyalty+-+Gamification+and+User+Behavior" title="Gamification">Gamification</a>
* Gigya's Activity Feed
* Upgraded <a href="https://developers.gigya.com/display/GD/Comments" title="Comments Version 2">Comments plugin (version 2)</a>
* Support connecting to an alternative data centers (e.g. Europe data center)
* Improved administration
* Integrated Google Analytics
* Gigya debug log

= 5.0 =
* The Plugin has been rewritten, providing improved architecture, administration and security.
* Gigya's <a href="https://developers.gigya.com/display/GD/Registration-as-a-Service" title="RaaS Integration">Registration-as-a-Service Integration</a>
* Gigya's <a href="https://developers.gigya.com/display/GD/R+and+R" title="Rating & Reviews">Rating & Reviews</a>
* Added SEO support in the Comments and Rating&Reviews plugins, meaning the comments/reviews content is searchable by the main search engines.
* All Gigya social plugins are provides as WP widgets that can be placed anywhere on your site.

= 5.1 =
* Support Gigya Widgets as shortcodes. Enabling you to embed the widgets anywhere on your site.
* Mapping Gigya User Fields to WordPress Fields via admin UI - You can configure which fields to map. As a consequence, when a user registers the data will be copied from the Gigya fields to the corresponding mapped WP fields.
* RaaS: Admin Login Roles configuration - enables you to configure which roles are permitted to login via regular WP login UI, rather then RaaS login.
* Social Login & Add-Connection widgets upgraded to V2
* Support WP 4.0 and 4.1
* Support Australia data center and "other" data centers.
* RaaS: updated to new screensets
* Support Follow Bar in administration

= 5.1.1 =
* Fixed a Gigya breaking change: Strings must be enclosed with quotes in search queries

= 5.2 =
* For security reasons, the Gigya Secret Key can now only be viewed or edited by privileged users. By default only by <strong>network admins</strong> (in a multi-site installation) or <strong>admins</strong> (in a single-site installation). Learn more in <a href="https://developers.gigya.com/display/GD/WordPress+Plugin#WordPressPlugin-RolesandPermissions">Roles and Permissions</a>.
* Security enhancements

= 5.2.2.2 =
* Bug fixes

= 5.5 =
* The GConnector authentication is now done using an application key and secret, rather than a partner secret, for security reasons. Note that in order for the new version to work, you must change the existing credentials (partner secret) to new ones (application key and secret).
* Custom field mapping: You can now flexibly map any Gigya field to a Wordpress field
* Users are now synced between Gigya and Wordpress based on their Gigya UID, and not on their email addresses.
* If a duplicate user is detected, they are no longer deleted. Instead, an error is displayed.

= 5.6 =
* Support for Gigya-led session management, including fixed and sliding user sessions
* Support for Russia and China data centers
* Removed Activity Feed from the connector
* Bug fixes

= 5.7 =
* The secret key is now fully encrypted
* Removed Follow Bar from the connector
* Bug fixes

= 5.7.1 =
* WordPress REST API extension for field mapping (read-only)
* Bug fixes

= 5.7.2 =
* Bug fixes

= 5.7.3 =
* It is now possible to enable setSSOToken through the global configuration

= 5.7.4 =
* Fixed session sync in SSO is now more accurate

= 5.7.5 =
* Fixed issue with logout

= 5.7.6 =
* Fixed multiple issues with plugin configuration on multi-site (network) setups
* Fixed edit user profile administrator page redirecting to the admin's own profile edit page

= 5.7.7 =
* Improved error handling on multisite
* Fixed an issue with login to a child site on multisite setups

= 5.7.8 =
* Fixed an issue configuring the Gigya plugin caused by internal changes

= 5.8 =
* It is now possible to load custom screen-sets from Gigya and place them anywhere on the website as widgets
* Changed license from GPL v2 to Apache 2.0

= 5.8.1 =
* Added license file and reference (minor)

== FAQ ==

Can I configure the design of the Gigya component?

Yes, the design is fully configurable. You can read more about configuring the Gigya component design in our online documentation <a href="https://developers.gigya.com/display/GD/WordPress+Plugin" title="Installation and Configuration guide">here</a>

How can I get support for the Gigya Plugin?

We provide extensive support to customers that implement the Gigya Plug-in. Please contact your Gigya Implementation Manager, or contact us at: support@gigya-inc.com

Is the Gigya service free?

The Gigya service is free for evaluation purposes.

Please contact Gigya via <a href="http://www.gigya.com/contact/" title="Contact Us">Contact Us</a> for information about pricing, services and technical support.

== License ==

Copyright (c) 2009– SAP SE or an SAP affiliate company. All rights reserved.
This file is licensed under the Apache Software License, v. 2 except as noted otherwise in the license.txt file.