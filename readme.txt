=== SAP Customer Data Cloud / Gigya - Social Infrastructure  ===

Contributors: SAP SE, konforti, luciodiri
Tags: CIAM, CIM, Registration, Social Login, Oauth, OpenSocial, Graph API, Facebook Connect, Linkedin, Twitter, authentication, OpenID,  newsfeed, tweet, status update, registration, social APIs, sharing, plugin, social bookmark, social network, Facebook, community, comments, reactions, game mechanics, register, SAP Customer Data Cloud, Social Infrastructure, feed
Requires at least: 4.2
Tested up to: 4.9
Stable tag: 5.8.1
License: Apache v2.0

Integrate your WordPress site with SAP Customer Data Cloud

== Description ==
SAP's Customer Identity Management Platform helps companies build better customer relationships by turning unknown visitors into known, loyal and engaged customers. With SAP Customer Data Cloud’s technology, businesses increase registrations and identify customers across devices, consolidate data into rich customer profiles, and provide better service, products and experiences by integrating data into marketing and service applications.

More than 700 of the world’s leading businesses such as Fox, Forbes, and Verizon rely on SAP Customer Data Cloud to build identity-driven relationships and to provide scalable, secure Customer Identity Management.

If you don't have an account yet, (https://console.gigya.com/).

**Add the Following Features to Your WordPress blog or website:**

**Customer Identity Management Suite**

* Registration-as-a-Service (RaaS) - Build, deploy and manage registration forms and profile management flows across your WordPress blog or Website.
* Social Login - Allow users to quickly sign up to your blog or website using a preferred social network account.
* Lite Registration - passwordless guest registration for subscribers and prospects.

**Consent Management**

* Supports compliance with data privacy laws: require consent to terms of service and privacy policies as a prerequisite for using some of your site services
* Additional optional consent statements allow flexibility in your relationship with the customer
* Communication preferences allow users to subscribe to various communication channels; with the option of requiring double opt-in as proof of the user's intention to subscribe
* Displays clearly to users which data was saved to their account, and provides the option to withdraw consent and manage communication preferences
* Consent Vault contains an audit of the customer's agreement to your site policies and the version to which they consented



For more information, installation steps and configuration options - please refer to SAP Customer Data Cloud's documentation:
(https://developers.gigya.com/display/GD/WordPress) SAP Customer Data Cloud's Wordpress plugin.

== Installation ==

You can read more about the installation steps and other advanced configuration options in SAP Customer Data Cloud's online documentation (https://developers.gigya.com/display/GD/WordPress)

1.	After downloading the SAP Customer Data Cloud plug-in, unpack and upload the folder to the the /wp-content/plugins/ directory on your blog.
2.	Go to the Plug-in tab in the WordPress administration panel, find the SAP Customer Data Cloud plug-in on the list and click Activate.
3.	Proceed to the plug-in settings page ("SAP CDC" item on the left sidebar) to configure your plug-in. The plug-in needs your API information in the SAP Customer Data Cloud settings menu, so please grab your API key, and secret code from the (https://console.gigya.com/).

For question about installations or configuration, please contact your account manager or contact our support via the support page on the Gigya site.

== Screenshots ==

1. WordPress Registration page with SAP Customer Data Cloud's Social Login
2. Registration-as-a-Service pop-up screen
3. SAP Customer Data Cloud's administration panel
4. SAP Customer Data Cloud widgets
5. Share with your social graph & comment on posts and articles.



== Changelog ==

= 2.0.5 =

* Support WordPress networks
* New customized Share bar including Facebook like button and Google+1.
* Bug fixes

= 2.0.6 =

* SAP CD's Comments plugin
* Bug fixes

= 3.0 =
* Option to choose the position of the share bar plugin.
* Improve the integration of SAP Customer Data Cloud comments with the WordPress comments.
* Call SAP's notifyLogin for user logging in with site credentials for better integration with SAP Customer Data Cloud plugins.
* Option to select which JQuery version to be used by SAP Customer Data Cloud plugin.
* Support SAP Customer Data Cloud Login widget as a shortcode - enables site owners to embed the Login widget anywhere on the site template without a sidebar.
* Bug fixes

= 3.0.5 =
* Security update
* Bug fixes


= 4.0 =
* SAP Customer Data Cloud's (https://developers.gigya.com/display/GD/Reactions)
* SAP Customer Data Cloud's (https://developers.gigya.com/display/GD/Loyalty+-+Gamification+and+User+Behavior)
* SAP Customer Data Cloud's Activity Feed
* Upgraded (https://developers.gigya.com/display/GD/Comments)
* Support connecting to an alternative data centers (e.g. Europe data center)
* Improved administration
* Integrated Google Analytics
* SAP Customer Data Cloud debug log

= 5.0 =
* The Plugin has been rewritten, providing improved architecture, administration and security.
* SAP Customer Data Cloud's (https://developers.gigya.com/display/GD/Customer+Identity).
* SAP Customer Data Cloud's (https://developers.gigya.com/display/GD/R+and+R).
* Added SEO support in the Comments and Rating&Reviews plugins, meaning the comments/reviews content is searchable by the main search engines.
* All SAP Customer Data Cloud social plugins are provides as WP widgets that can be placed anywhere on your site.

= 5.1 =
* Support SAP Customer Data Cloud Widgets as shortcodes. Enabling you to embed the widgets anywhere on your site.
* Mapping SAP Customer Data Cloud User Fields to WordPress Fields via admin UI - You can configure which fields to map. As a consequence, when a user registers the data will be copied from the Gigya fields to the corresponding mapped WP fields.
* RaaS: Admin Login Roles configuration - enables you to configure which roles are permitted to login via regular WP login UI, rather then RaaS login.
* Social Login & Add-Connection widgets upgraded to V2
* Support WP 4.0 and 4.1
* Support Australia data center and "other" data centers.
* RaaS: updated to new screensets
* Support Follow Bar in administration

= 5.1.1 =
* Fixed a SAP Customer Data Cloud breaking change: Strings must be enclosed with quotes in search queries

= 5.2 =
* For security reasons, the SAP Secret Key can now only be viewed or edited by privileged users. By default only by <strong>network admins</strong> (in a multi-site installation) or <strong>admins</strong> (in a single-site installation). Learn more in (https://developers.gigya.com/display/GD/WordPress+Plugin#WordPressPlugin-RolesandPermissions) Roles and Permissions</a>.
* Security enhancements

= 5.2.2.2 =
* Bug fixes

= 5.5 =
* The GConnector authentication is now done using an application key and secret, rather than a partner secret, for security reasons. Note that in order for the new version to work, you must change the existing credentials (partner secret) to new ones (application key and secret).
* Custom field mapping: You can now flexibly map any SAP Customer Data Cloud field to a Wordpress field
* Users are now synced between SAP Customer Data Cloud and Wordpress based on their SAP Customer Data Cloud UID, and not on their email addresses.
* If a duplicate user is detected, they are no longer deleted. Instead, an error is displayed.

= 5.6 =
* Support for SAP Customer Data Cloud-led session management, including fixed and sliding user sessions
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
* Fixed an issue configuring the SAP Customer Data Cloud plugin caused by internal changes

= 5.8 =
* It is now possible to load custom screen-sets from SAP Customer Data Cloud and place them anywhere on the website as widgets
* Changed license from GPL v2 to Apache 2.0

= 5.8.1 =
* Added license file and reference (minor)

== FAQ ==

Can I configure the design of the SAP Customer Data Cloud component?

Yes, the design is fully configurable. You can read more about configuring the SAP Customer Data Cloud component design in our online documentation (https://developers.gigya.com/display/GD/WordPress)

How can I get support for the SAP Customer Data Cloud Plugin?

We provide extensive support to customers that implement the SAP Customer Data Cloud Plug-in. Please contact your SAP Customer Data Cloud Implementation Manager, or contact us at: support@gigya-inc.com

Please contact SAP via (https://www.sap.com/corporate/en/company/office-locations.html) for information about pricing, services and technical support.

== License ==

Copyright (c) 2009– SAP SE or an SAP affiliate company. All rights reserved.
This file is licensed under the Apache Software License, v. 2 except as noted otherwise in the license.txt file.