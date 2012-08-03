=== Virtual Theme ===
Contributors: mobilesentience
Donate link: http://www.mobilesentience.com/software/oss/virtual-theme/
Tags: virtual path, URL, url, virtual, path, switch, switcher, address, style, CSS, theme, themes, skin, branding, affiliate
Requires at least: 2.2
Tested up to: 3.1
Stable tag: 1.0.14

Virtual Theme allows you to switch themes based on virtual paths, making an entire site accessible through multiple themes based on their URL prefix.

== Description ==

Virtual Theme allows you to switch themes based on virtual paths. This makes makes an entire WordPress site accessible through multiple themes based on their URL prefix.  This enables you to present you site with multiple themes based on how the site is accessed without the need for separate hostnames or ssl certificates.  (i.e. www.example.com and  www.example.com/mobile). A specific theme template can be assigned to a specific virtual path. For instance, Mobile Sentience is using this plugin switch to a theme that is formated to work with Facebook's new iframe landing pages when accessed via http://www.mobilesentience.com/facebook.  Permalinks must be enabled though no additonal changes need to be made to your web server, .htaccess file or domain name server.  You can also add custom variables with unique values for each virtual path.  You can access the custom variables in your themes, they are saved as an array in the get vaariable name 'VirtualThemeVariables'.   $_GET["VirtualThemeVariables"]['testvar'];

Virtual Theme works together with [Advertwhirl](http://wordpress.org/extend/plugins/advertwhirl/) to it simple to allow a customer to make their own "virtual brand" of your WordPress site.

Known Issues:

* Fixed rewrite rules to be more specific so permalinks that contain part of a virtual path don't match
* Virtual paths must be chosen to not collide with existing permalinks (this is different then above. Above is a fix where the permalink is not equal to the vpath but contains the vpath.  The two still can not be 100% equivalent)
* if you are upgrading from version 1.0.10 or older you may have to deactivate the plugin and then reactivate it to get rewrites to work, also please check to see that your vpaths were properly imported.  Both have been tested and should work, but in a few cases this failed in testing.  Workarounds have been tested and should be working.  If anyone experiences a problem with this please report it.
* There have been issues [reported](http://wordpress.org/support/topic/plugin-virtual-theme-error-404?replies=5) for WordPress sites setup for multisite networks.  Version 1.0.12 is most of the way to fixing this, waiting on feedback to see if an extra rewrite rule or two need to be added.

== Installation ==

Through WordPress plugin gallery using built-in installer (preferred)

1. Login as an admin
1. Go to “Plugins” and click “Add New”
1. Enter “Virtual Theme” in the search terms and click “Search”
1. Click “Install Now”
1. When prompted “Are you sure you want to install this plugin” click “Ok”
1. Fill out your “Connection Information” and click “Proceed”
1. When WordPress is finished installing the plugin click “Activate”
1. Go to the Virtual Theme settings menu setup your campaigns

Through WordPress built-in installer if you already have the file downloaded

1. After plugin zip file is downloaded Login as an admin
1. Go to “Plugins” and click “Add New”
1. Click “Upload”
1. Click “Choose File” and find the downloaded plugin zip file
1. Click “Install Now”
1. Fill out your “Connection Information” and click “Proceed”
1. When WordPress is finished installing the plugin click “Activate”
1. Go to the Virtual Theme settings menu setup your campaigns

Manual method

1. Download plugin zip file from WordPress directory or the [Mobile Sentience Virtual Theme page](http://www.mobilesentience.com/software/oss/virtual-theme/)
1. Unzip plugin file
1. Upload the ‘advertwhirl’ directory and all of its files to the ‘/wp-content/plugins/’ directory of your WordPress site
1. Activate the plugin through the ‘Plugins’ menu in WordPress
1. Go to the Virtual Theme settings menu to setup your campaigns

== Frequently Asked Questions ==

= Can I specify a different Blog Title and Tagline for each virtual path? =

Yes. There are options to enter a Blog Title and Tagline that will override what is specified in 'General Settings' for a given virtual path.

= Can I use the same theme for more then one virtual path? =

Yes. Use the Add Virtual Path form to specify a single virtual path and select the theme to use from theme drop down menu. 

= Can I use a different set of widgets for each virtual paths? =

No. Widgets are defined at the site level and will appear on all supported themes. However, almost anything is possible from within theme templates and you could just alter a theme template to omit or allow certain widgets manually.

== Screenshots ==

1. The Virtual Theme control panel.
2. Edit custom variables
3. Edit Virtual Theme

== Changelog ==
= 1.0.14 =
* Further bug fixs for bugs introduced in 1.0.12 for fresh installs

= 1.0.13 =
* Fixed a bug introduced with the config changes in 1.0.12 that broke things on fresh installs

= 1.0.12 =
* Updating from version 1.0.10 or older should now force a flush of the rewrite rules
* Updating from version 1.0.10 or older should now import all of your old options with no problem
* Fixed bug where a portion of a permalink matched a vpath and the vpath was selected

= 1.0.11 =
* Added multisite support

= 1.0.10 =
* fixed a bug in GetCustomVariable() static api function

= 1.0.9 =
* fixed a bug when wordpress is not installed in the web root but is installed in a sub-directory

= 1.0.8 =
* added support for integration with [Advertwhirl](http://wordpress.org/extend/plugins/advertwhirl/)

= 1.0.7 =
* updated ad code to pull from advertwhirl
* preparing to switch to using wordpress rewrite hooks

= 1.0.6 =
* fixed bug where strpos finds vpath at postion 0 and so evaluates as false

= 1.0.5 =
* Fixed bug where Virtual Paths were not working if Wordpress was installed in a subdirectory and not the web servers root directory

= 1.0.4 =
* Added support to copy an existing virtual path to a new virtual path (good if you only want a small change, such as to a custom variable)
* Updated screenshot of Virtual Theme control panel
* Cleaned up ad sponshorship

= 1.0.3 =
* Fixed screenshots

= 1.0.2 =
* Added support for custom variables
* Added links for twitter, facebook
* Added ad based sponsorship

= 1.0.1 =
* Cleaned up html
** Merged add virtual path form with edit table

= 1.0 =
* Initial release

== Upgrade Notice ==
= 1.0.14 =
* Bug fixs

= 1.0.13 =
* Fixed a bug introduced with the config changes in 1.0.12 that broke things on fresh installs

= 1.0.12 =
* Updating from version 1.0.10 or older should now force a flush of the rewrite rules
* Updating from version 1.0.10 or older should now import all of your old options with no problem
* Fixed bug where a portion of a permalink matched a vpath and the vpath was selected

= 1.0.11 =
* Added multisite support

= 1.0.10 =
* fixed a bug in GetCustomVariable() static api function

= 1.0.9 =
* fixed a bug when wordpress is not installed in the web root but is installed in a sub-directory
* doesn't address multisite networks bug

= 1.0.8 =
* added support for integration with [Advertwhirl](http://wordpress.org/extend/plugins/advertwhirl/)

= 1.0 =
* This is the initial release.

== Support ==
Someone marked the plugin as not working without submitting any kind of bug report.  If you have any problems or usage questions you can report them [here](http://www.mobilesentience.com/support/?mingleforumaction=viewforum&f=11.0 "Virtual Theme Support Forum")

== Acknowledgements ==
Virtual theme is based on the Domain Theme plugin, by Stephen Carroll

== Blog Posts ==
* [Reseller branding of your WordPress site](http://www.mobilesentience.com/2012/04/24/reseller-branding-of-your-wordpress-site/)
