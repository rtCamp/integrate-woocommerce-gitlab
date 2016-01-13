=== Integrate Woocommerce Gitlab ===

Contributors: [rtcamp](http://profiles.wordpress.org/rtcamp), [desaiuditd](http://profiles.wordpress.org/desaiuditd)
Donate link: http://rtcamp.com/donate/
Tags: WooCommerce, git, WordPress, gitlab
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: WordPress 3.0
Tested up to: 4.4
Stable tag: 1.0

It provides a simple way to connect GitLab to WooCommerce. 

== Description ==

WooCommerce GitLab add-on provides a simple way to connect GitLab to WooCommerce. Customers will get access to source code repositories as a Guest, Reporter, Developer or Master right after buying your products.

WooCommerce GitLab add-on allows you to provide repository access to customers for a particular product. It simply creates a new user (if user doesn’t exist) and provides access to code repository.


=== Features ===

* Track your premium customers with ease
* Provides way to control access on GitLab
* Send access email right after completion of order

=== Prerequisites ===

1. You will have to add [WooCommerce](https://wordpress.org/plugins/woocommerce/) plugin before using WooCommerce GitLab plugin.
2. GitLab
3. GitLab should be installed on a Fully Qualified Domain. If you want to setup fresh GitLab for this plugin, here are the [GitLab Installation Steps](http://github.com/gitlabhq/gitlabhq/blob/master/doc/install/installation.md) for production servers.

=== Settings == 

* Go to WooCommerce > Settings > GitLab.
* Under GitLab Endpoint, enter your GitLab domain URL with api and version( e.g. http://example.com/api/v3/ )
* Log in to your GitLab account, go to Profile Settings > Account and copy private token.
* Retun back to WooCommerce > Settings > GitLab, under GitLab Private Token, enter private token which you have copied from GitLab in step 6.
* Under GitLab Forgot Password Link, enter GitLab forget password link( e.g. http://example.com/users/password/new ). It will be added in the mail template when GitLab sends confirmation mail for repo access.
* Under GitLab Default Access, set default access for your users who will purchasing the product. Choose from Guest, Reporter, Developer and Master.
* Under GitLab Admin Email, enter the email address which you want to notify each time someone attains access to GitLab after purchasing GitLab-integrated products.
* Use the Test Connection button to test your GitLab settings to confirm synchronization with WooCommerce.
* Click Save Changes.

== Installation ==

Follow these simple steps to get Wufoo To Gravity Forms Importer plugin.

Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
* Activate the plugin through the 'Plugins' screen in WordPress
* Use the Settings->Plugin Name screen to configure the plugin
* (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)


== Frequently Asked Questions ==

= Q. What is a GitLab API Endpoint?

GitLab API Endpoint is your GitLab url suffixed with /api/[GitLab API Version] ( e.g., http://git.example.com/api/v3. ). GitLab API version is defined in lib/api/api.rb.

= Q. How can I get GitLab Private Token?

Your Private Token is available on your profile page. To get your Private Token, go toProfile Settings > Account.

= Q. How to get GitLab Forgot Password Link?

GitLab Forget Password Link is used to generate new password when someone forgets GitLab password. To get GitLab Forget Password link, go to login page of your GitLab URL and click on Forget Your Password? button.

= Q. What is GitLab Default Access?

GitLab Default Access is access role provided for product repository to your customers. Available Access Levels are Guest, Reporter, Developer or Master.

= Q. What is a GitLab Notification Email?

You get an email from GitLab when someone gets access for the project repository.



== Changelog ==

= 1.1.0 =
* Update Checker Libs

= 1.0.8 =
* Update Product Nonce bug Fixed

= 1.0.7 =
* Version change

= 1.0.6 =
* Version change

= 1.0.5 =
* Gitlab Username Creation Fallback added

= 1.0.4 =
* Email Messages Spellings Fixed

= 1.0.3 = 

* Gitlab Admin Email Setting Added / User Creation-Project.
* Access Process Changed on Order Complete

= 1.0.2 =

* Access Permission on Gitlab Error Fixed

= 1.0.1 =
* Refactor-Coding Standards

= 1.0 =
* Initial Release 

== Upgrade Notice ==

= 1.0 =
Requires WordPress 3.6 or higher.




