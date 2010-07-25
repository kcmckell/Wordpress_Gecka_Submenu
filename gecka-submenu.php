<?php
/*
Plugin Name: Gecka Submenu
Plugin URI: http://github.com/loxK/Wordpress_Gecka_Submenu
Description: Provide submenu and autosubmenu widgets, submenu shortcode and submenu template tag for the new wordpress 3.0 menu system
Version: 0.3
Author: Laurent Dinclaux
Author URI: http://gecka.nc
Licence: GPL2
*/

/* Copyright 2010  Gecka SARL (email: contact@gecka.nc). All rights reserved

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('GKSM_PATH' , WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)) );
// define('GKSM_URL'  , WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) );

// global var used by wp_get_nav_menu_items filter 
$GKSM_ID = $GKSM_MENUID = null;

require GKSM_PATH . '/gecka-submenu.class.php';

// Instantiate the class
if (class_exists('Gecka_Submenu')) {
    if (!isset($GkSm)) {
        
    	include GKSM_PATH . '/models/Submenu.php';
    	
    	$GkSm = new Gecka_Submenu();
        
        include GKSM_PATH . '/template-tags.php';
    }
}
