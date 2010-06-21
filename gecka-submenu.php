<?php
/*
Plugin Name: Gecka Submenu
Plugin URI: http://gecka.nc
Description: Displays submenu
Version: 0.1 beta
Author: Laurent Dinclaux
Author URI: http://gecka.nc/

/* 
Copyright 2010  Gecka SARL (email: contact@gecka.nc)
*/


define('GKSM_PATH' , WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)) );
// define('GKSM_URL'  , WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) );

// global var used by wp_get_nav_menu_items filter 
$GKSM_ID = $GKSM_MENUID = null;

require_once GKSM_PATH . '/gecka-submenu.class.php';

// Instantiate the class
if (class_exists('Gecka_Submenu')) {
    if (!isset($GkSm)) {
        $GkSm = new Gecka_Submenu();
    }
}

?>
