=== Plugin Name ===
Contributors: Laurent Dinclaux
Tags: nav menu, 3.0, submenu, sub-menu, child, child menu, portion of menu, menu portion
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 0.5

Autopupulate nav menu items with sub pages and provides an advanced custom menu widget, along with shortcodes and a template tag for the new wordpress 3.0 menu system.

== Description ==

Autopupulate nav menu items with sub pages and provides an advanced custom menu widget, along with shortcodes and a template tag

For each page menu item you add to any nav menu, you can tell it to autopopulate its child items with its sub pages. It saves a lot of hassle when having a lot of pages.

The advanced custom menu widget will automatically generate a sub-menu starting from the top parent menu item of currently viewed page, from the currently viewed page or from any defined menu item.
It has other options like Showing menu item description, depth or automatically showing the title of the top submenu level.

See Installation to for shortcode and template tag details.

== Installation ==

1. Upload `gecka-submenu` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

Now you can use the provided widgets, the [submenu] and [menu] shortcode and the 'submenu' template tag action.

The shortcode and template tag options are the same:
* "menu" : the nav menu ID (mandatory)
* "submenu" : the child menu item id to start menu at, will try to guess it using current post if not provided
* "auto" : automatically generates a submenu starting from the current post menu item's top parent item
* "show_description" if set to true or 1, will show the menu items description in the <a> tag in a span with 'description' css class
* "depth" specify the depth of the submenu

Examples:
[submenu menu=3] : will show a submenu starting from current post/page
[submenu menu=3 submenu=18] : will show a submenu starting from menu item id 18
[submenu menu=3 auto=1] : will show a submenu starting from the current post/page menu item's top parent item

do_action('submenu', array( 'menu'=>3, 'depth'=>1);
do_action('submenu', 'menu=3&submenu=18&show_description=1&depth=2');

== Frequently Asked Questions ==

= What if I have the same page linked to two or more menu items ? =

Well this is where it can cause you problems with auto sub-menu and thus may shows the wrong menu portion. 

To get the menu item linked to current post, I use the wp_get_associated_nav_menu_items() function witch returns all menu items linked to a post/page. So I simply use the first value return and it may not be the menu branch you were expecting. To avoid that, avoid linking the same page to more than one menu item.

== Screenshots ==

1. Auto Sub-menu widget
2. Sub-menu widget
3. Autopopulate child pages option

== Changelog ==

= 0.5 =
* Rewritten the way nav menus get autopopulated with child items
* Merged the two widgets and added some more options to it
* Other fixes

= 0.4.2 =
* More fixes
* Menu parameter is no more mandatory, gets the lowest ID menu if not set 

= 0.4.1 =
* Fixed bug in submenu builder

= 0.4 =
* Fixed some notice errors
* Experimental functionnality to autopopulate a page menu item with its subpages for WP 3.0 nav menu system

= 0.3 =
* Added template tag and shortcode support

= 0.2 =
* Bugs fixes
* Localization support (French added).

= 0.1 =
* First version.


