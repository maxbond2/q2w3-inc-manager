=== Code Insert Manager (Q2W3 Inc Manager) ===
Contributors: Max Bond
Tags: q2w3, code insert, code include, ads, header, footer, html, css, javascript, php, widget, shortcode, custom taxonomy, custom post type, custom post type archive, post format
Requires at least: 3.1
Tested up to: 5.4.1
Stable tag: 2.5.1

This plugin allows you to insert html, css, javascript and PHP code to public WordPress pages. 

== Description ==

[youtube http://www.youtube.com/watch?v=JPhX8D7xDzo]

For more info visit [plugin homepage](http://www.q2w3.ru/code-insert-manager-wordpress-plugin/)

Areas of the page where you can insert code:

1. Header (between 'head' tags)
2. Footer (bottom of the page)
3. Before page content (before main WP loop)
4. After page content (after main WP loop)
5. Before post content
6. After post excerpt (after 'more' tag)
6. After post content
7. Manual. Place function, which displays code, directly in your theme file(s)	
8. `Widget`. Allows you to create custom widgets
9. `Shortcode`. Allows you to create custom shortcodes. Since version 2.3.0 added ability to insert shortcodes in comments

Display filters:

* Insert on pages - here you can select pages where you code CAN BE shown. 
* Exclude pages - pages where you code CAN NOT be shown. 
* Hide from user - allows to hide code from specified user groups/roles. 

Page selectors:

* `Custom taxonomy` pages are available since version 2.0.0.
* `Custom Post Type` pages - version 2.0.0
* `Post formats` - version 2.1.0.
* `Custom Post Type Archive` pages - version 2.2.0

Other options:

* Priority number - determines display order of the Inserts placed in the same location.
* Align - allows you to set horizontal align for inserted code. Useful for aligning Google AdSense blocks.

== Installation ==

1. Check minimal system requirements: WordPress 3.1, PHP 5.5
2. Follow standard WordPress plugin installation procedure
3. Activate the plugin through the Plugins menu in WordPress

== Screenshots ==

1. Code Insert locations
2. Inserts table
3. Insert options

== Changelog ==

= 2.5.1 =
* Updated plugin core, fixed most php warnings and notices
* Added support of PHP7
* Added standard plugin uninstall script
* Added feature to make head and footer inserts from the post editor 
* Removed option to use [include] shortcode in comments
* Disabled PHP eval by default! To enable PHP eval add `define('Q2W3_PHP_EVAL', true);` in wp_config.php. Use with caution! In multiuser or multisite enviroments you can grant PHP exec rights for non admin users!!!

= 2.3.3 =
* [Fixed shortcode executing problem](http://wordpress.org/support/topic/code-insert-manager-not-executing-shortcodes) 

= 2.3.2 =
* ZeroClipboard updated to version 1.1.7. This version fixes vulnerability described [here](http://seclists.org/fulldisclosure/2013/Feb/103).

= 2.3.1 =
* [Fixed problem with broken category feeds](http://wordpress.org/support/topic/plugin-code-insert-manager-q2w3-inc-manager-kills-the-feed)

= 2.3.0 =
* Added ability to insert shortcodes in comments. Option must be enabled in Code Insert Manager -> Settings page

= 2.2.0 =
* Added support for Custom Post Type Archive pages
* Fixed small bug with Opera multiple select

= 2.1.3 =
* Added Italian translation

= 2.1.2 =
* Fixed conflict with [Find and replace](http://wordpress.org/extend/plugins/find-replace/) plugin

= 2.1.1 =
* Added German translation

= 2.1.0 =
* `Post Formats` are now available for page filters
* To admin page added search form and capability to filter table by status (Active/Disabled)

= 2.0.1 =
* Plugin renamed to `Code Insert Manager`
* Added support for `custom post types` (support for each post type must be enabled on plugin settings page)
* Added support for `custom taxonomies` (support for each taxonomy must be enabled on plugin settings page)
* Added new insert location - `After post excerpt`
* `Hide from admin` option is upgraded to `Hide from user`. Now you can specify any user group, not only Administrator. Plus there is one virtual user group - visitor (for not logged in users).  
* Admin menu entry moved to top level.

= 1.3.1 =
* Added French translation.

= 1.3.0 =
* Added new insert location - shortcode.

= 1.2.4 =
* Fixed php evaluation error in Manual include mode.
* Plugin was successfully tested in WordPress 3.0

= 1.2.3 =
* Improved page detection on themes with modified loop.

= 1.2.2 =
* Fixed high memory usage on blogs with large number of posts/categories (more than 1000).

= 1.2.1 =
* Fixed broken links when WordPress installed in subdirectory.

= 1.2.0 =
* Now the plugin is WordPress MU compatible (requires WP MU 2.8.1 or higher).

= 1.1.0 =
* New. PHP code insertions now supported.
* New. Align option added.  

= 1.0.1 =
* Fixed 'Create New' button malfunction in Opera and Firefox.
* Fixed `Parse error: syntax error, unexpected T_PROTECTED` in some environments. 

= 1.0 =
* First public release.
