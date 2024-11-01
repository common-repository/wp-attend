<?php

/*
Plugin Name: WP Attend
Plugin URI:  https://wp-attend.com
Description: Wordpress plugin for registering attendances
Version:     1.0.3
Author:      Rutger De Wilde
Author URI:  http://bitsandarts.be
Text Domain: wp-attend
Domain Path: /languages
License:     GPL2

This code is released under the GPL licence version 3 or later, available here
 * https://www.gnu.org/licenses/gpl.txt

 *
 * @package WP_Attend
 */

 if( ! defined( 'ABSPATH' ) ){
	 exit;
 }

 define ('WP_ATTEND_PLUGIN_VERSION', '1.0.0');
 define ('WP_ATTEND_PLUGIN_DIR', plugin_dir_path(__FILE__) );
 define ('WP_ATTEND_PLUGIN_URL', plugin_dir_url(__FILE__) );
 define('WP_ATTEND_PLUGIN_NAME', plugin_basename(__FILE__) );
 define('WP_ATTEND_PLUGIN_DIRNAME', 'WP-attend' );

 // include the main class
 require plugin_dir_path( __FILE__ ). 'includes/class-wp-attend.php';

 // Main instance of plugin_dir_path
 function WP_Attend(){
	 return WP_Attend::get_instance();
 }


 $wp_attend = WP_Attend();
 $wp_attend->includes();
 $wp_attend->register();
