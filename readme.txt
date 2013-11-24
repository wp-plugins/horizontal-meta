=== Horizontal Meta ===
Contributors: nathanfranklinau
Tags: horizontal meta, eav, relational meta, fast queries, slow queries, speed up wordpress, slow meta_query, meta_query
Author: Nathan Franklin
Author URI: http://www.nathanfranklin.com.au
Plugin URI: http://www.horizontalmeta.com/
Requires at least: 3.2
Tested up to: 3.7.1
Stable tag: trunk
Homepage: http://www.horizontalmeta.com/
Version: 2.07b
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change the structure of important meta data fields to a horizontal/relational structure and give WordPress meta queries a performance kick.

== Description ==
Give WordPress meta queries a performance kick. Change the structure of important meta data fields to a horizontal/relational structure to overcome the performance restrictions that exist in the current meta structure.

Horizontal Meta creates additional relational tables in monitors specific meta keys for updates and deletions and will copy any update into the relational table. Horizontal Meta can also override the default WordPress queries linking to the WordPress meta table to reroute them to its own table structure. This can significantly increase the speed of meta queries for larger sites that use heavy meta querying and also gives way to better custom SQL queries for extracting meta data out of WordPress.

= What it's got =
* Create Data-typed mappings in a horizontal/relational structure.
* Includes short text (string) (free), date (premium), time (premium), numeric (premium), decimal (premium), text (premium), long text (premium) data types with more to come.
* Powerful user interface to review data stored in meta keys and manage the mappings stored in the system.
* Manage mappings with the Horizontal Meta API
* Works with User meta and Post meta
* Compatible with multisite.
* Beta version provides ability to create up to 10 mappings with string data type.

= Perform Faster Meta Queries =
Access your post and user meta data faster than ever before! Horizontal Meta takes a snapshot of your data and makes it accessible at record speeds!

= Powerful Interface to Manage Mappings =
Horizontal Meta comes with a powerful interface to manage your meta mappings. Create new post and user mappings and reviewing data within each mapping are just a few of the features available.

= API for Developers =
Create mappings on the fly with the develops API.

= Data Typed Meta Data =
Multiple data types allow you to perform natively data typed mysql queries without the need to cast your data. Horizontal Meta supports string, int*, decimal*, date* & datetime* with more to come soon. (* Requires premium version)

= Provides a Relational Table for Writing Custom Queries =
Horizontal Meta creates an additional relational table which can be used to create custom sql queries without the need to join to the WordPress meta table.

= Documentation =
* [API](http://horizontalmeta.com/documentation/api)
* [Example](http://horizontalmeta.com/documentation/examples/perform-a-post-query-using-mapped-meta-keys)

= Please Vote and Enjoy =
Your votes really make a difference! Thanks.

= Policy Documents =
* Terms and Conditions that you agree to by using this plugin http://horizontalmeta.com/terms-of-service/
* Privacy Policy that you agree to by using this plugin http://horizontalmeta.com/privacy-policy/

= PREMIUM VERSION =
Buy the Premium Version! http://sllwi.re/p/we

= NOTE =
This software is considered to be in BETA. Please ensure the plugin works as expected carefully and please report any bugs.

== Installation ==

1. Upload 'horizontal-meta' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Settings -> Horizontal Meta and click Create Mapping.
4. Enter the details of the mapping you wish to create.


== Frequently Asked Questions ==

= Q. Is Horizontal Meta guaranteed to improve speed of my website? =
A. No. Horizontal Meta should be tested with your installation and your WordPress configuration. For large sites, Horizontal Meta will most likely improve performance though not guaranteed. Horizontal Meta should only be used in conjunction with good coding practises. This includes the correct structuring of data types & the correct use of taxonomies. Meta Data should not be considered a quick fix, one solution fits all plugin. Advanced performance reviews are available for a fee by emailing me@nathanfranklin.com.au.

= Q. The data in the WordPress table is different from the data in the Horizontal Meta table. My data is not synchronising correctly. What do I do? =
A. This may be because the meta key you are watching is being directly updated in the database rather than using the WordPress API. Your meta key may not be compatible with Horizontal Meta. If you are a developer, you may try to trace through the code to find out where the meta key is being updated. If the meta key is created an updated by a plugin, you may also send a request to the plugin developer and request they use the WordPress API to make updates and additions.

= Q. I have just installed the plugin and my website is still slow. What do I do? =
A. By default when the plugin is first installed, Horizontal Meta will not intercept your meta key queries unless you prefix each meta key with \_horzm\_. This is the recommended method of integrating Horizontal Meta into your website. You may also go to Advanced Settings and set the Intercept Meta Keys option to Yes. With this option set, Horizontal Meta will then reroute any keys that are being watched to Horizontal Meta. You should also double check to ensure you have mapped all the meta keys required for your queries. If you are trying to use Horizontal Meta with get_posts(), you must set the 'suppress_filters' arg to false, or else Horizontal Meta will not be called. If you have done this then perhaps Horizontal Meta is not going to increase the performance of your website and you should look to other solutions.

= Q. I am receiving the message 'Column could not be allocated. Resource limit reached.' =
A. The beta 2.0 version allows you to create upto 10 post mappings and 10 user mappings with a data type of string. There is an additional plugin to extend the resource limitation and add additional data types, however there is a fee for this plugin. You can purchase the premium version of the plugin here: http://sllwi.re/p/we

== Screenshots ==

1. Remove Mappings Screen

2. Create Mapping Screen with Data Compatibility Test as Pass.

3. Create Mapping Screen with Data Compatibility Test as Caution.

4. Data Management Screen. Check if data is in sync between WordPress and Horizontal Meta.

5. Options Screen.

== Changelog ==

= 2.07b free =
* Fixed Settings Update bug.

= 2.06b free =
* Added license code field for when premium extender plugin is installed and activated.

= 2.05b free =
* Added data type labels on the create mappings screen.
* Created scope for text & longtext data types in premium version.

= 2.01b free =
* Removed debug log messages in pre-release.

= 2.0b free =
* Public Release

= 1.0b lite =
* Private Release