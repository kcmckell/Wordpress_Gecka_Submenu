=== Plugin Name ===
Contributors: Laurent Dinclaux
Tags: nav menu, 3.0, submenu, sub-menu, child, child menu, portion of menu, menu portion
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 0.3

Provides submenu and autosubmenu widgets for the new wordpress 3.0 menu system.

== Description ==

Provides submenu and autosubmenu widgets for the new wordpress 3.0 menu system.

The Auto Sub-Menu widget will automatically generate a sub-menu starting from the top parent menu item of currently viewed page.

For the Sub-Menu widget, you select a menu and a child menu item. A sub-menu starting from that child will be generated. 

== Installation ==

1. Upload `gecka-submenu` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

Now you can use the two provides widgets, or the [submenu] shortcode or the submenu template tag action.

The shortcode and template tag options are the same:
* **menu** : the nav menu ID (mandatory)
* **submenu** : the child menu item id to start menu at, will try to guess it using current post if not provided
* **auto** : automatically generates a submenu starting from the current post menu item's top parent item
* **show_description** if set to true or 1, will show the menu items description in the <a> tag in a span with 'description' css class
* **depth** specify the depth of the submenu

Exemples:
[submenu menu=3] : will show a submenu starting from current post/page
[submenu menu=3 submenu=18] : will show a submenu starting from menu item id 18
[submenu menu=3 auto=1] : will show a submenu starting from the current post/page menu item's top parent item

== Frequently Asked Questions ==

= What if I have the same page linked to two or more menu items ? =

Well this is where it can cause you problems with auto sub-menu and thus may shows the wrong menu portion. 

To get the menu item linked to current post, I use the wp_get_associated_nav_menu_items() function witch returns all menu items linked to a post/page. So I simply use the first value return and it may not be the menu branch you were expexcting. To avoid that, avoid linking the same page to more than one menu item.

= What about foo bar? =

Answer to foo bar dilemma.



== Screenshots ==

1. Auto Sub-menu widget
2. Sub-menu widget

== Changelog ==

= 0.3 =
* Added template tag and shortcode support

= 0.2 =
* Bugs fixes
* Localization support (french added).

= 0.1 =
* First version.
