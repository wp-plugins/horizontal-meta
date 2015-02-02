=== Horizontal Meta ===
Contributors: nathanfranklinau
Tags: horizontal meta, eav, relational meta, fast queries, slow queries, speed up wordpress, slow meta_query, meta_query
Author: Nathan Franklin
Author URI: http://www.nathanfranklin.com.au
Requires at least: 3.2
Tested up to: 4.0.1
Stable tag: trunk
Homepage: http://www.horizontalmeta.com/
Version: 2.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change the structure of important meta data fields to a horizontal/relational structure and give WordPress meta queries a performance kick.

== Description ==
Give WordPress meta queries a performance kick. Change the structure of important meta data fields to a horizontal/relational structure to overcome the performance restrictions that exist in the current meta structure.

Horizontal Meta creates additional relational tables in monitors specific meta keys for updates and deletions and will copy any update into the relational table.

NOTE: Horizontal Meta is currently NOT compatible with advanced meta querying features available in WordPress 4.1 and later!!! You will still be able to use Horizontal Meta for meta data extraction, but Horizontal Meta will not be able to rewrite complex queries. This may be available sometime in the future.

= What it's got =
* Create Data-typed mappings in a horizontal/relational structure.
* Includes short text (string), date, time, numeric, decimal, text, long text data types.
* Powerful user interface to review data stored in meta keys and manage the mappings stored in the system.
* Manage mappings with the Horizontal Meta API
* Works with User meta and Post meta
* Compatible with multisite.

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
A. By default when the plugin is first installed, Horizontal Meta will not intercept your meta key queries unless you prefix each meta key with \_horzm\_. This is the recommended method of integrating Horizontal Meta into your website. You may also go to Advanced Settings and set the Intercept Meta Keys option to Yes. With this option set, Horizontal Meta will then reroute any keys that are being watched to Horizontal Meta. You should also double check to ensure you have mapped all the meta keys required for your queries. If you are trying to use Horizontal Meta with get_posts(), you must set the 'suppress_filters' arg to false, or else Horizontal Meta will not be called. If you have done this then perhaps Horizontal Meta is not going to increase the performance of your website and you should look to other solutions. NOTE: Horizontal Meta currently does not support the advanced querying features available in WordPress 4.1 and later! This may be available in the future.

== Screenshots ==

1. Remove Mappings Screen

2. Create Mapping Screen with Data Compatibility Test as Pass.

3. Create Mapping Screen with Data Compatibility Test as Caution.

4. Data Management Screen. Check if data is in sync between WordPress and Horizontal Meta.

5. Options Screen.

== Changelog ==

= 2.3.1 =
* FIXED: PHP Notices when debug mode is activated.

= 2.3.0 =
* NOTE: Horizontal Meta is not compatible with advanced querying features available in WordPress 4.1 and later.
* ADDED: Premium datatypes date, time, numeric, decimal, text, long text data types.
* REMOVED: Restrictions to limit mappings. You can now create unlimited mappings.

= 2.2.1 free =
* ADDED: Exists compare operator
* FIXED: Bug when saving a value that needs to be serialized. The value wasn't serialized but instead set the field to an empty value.

= 2.2 free =
* FIXED: Bug using get_called_class() (php pre version 5.3).
* FIXED: Bug in where clause generation. Replaced empty() with isset().

= 2.1 free =
* Officially out of BETA!

= 2.08b free =
* Added premium upgrade options.
* Fixed a minor display bug on the create mappings page.

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