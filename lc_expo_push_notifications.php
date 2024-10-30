<?php
/* 
Plugin Name: LC Expo Push Notifications
Plugin URI: http://lcweb.it/
Description: Push notifications made simple for your Expo (React Native) apps
Author: Luca Montanari aka LCweb
Version: 1.0
Author URI: https://lcweb.it/
*/  




/////////////////////////////////////////////
/////// MAIN DEFINES ////////////////////////
/////////////////////////////////////////////

// plugin path
$wp_plugin_dir = substr(plugin_dir_path(__FILE__), 0, -1);
define('LCEPS_DIR', $wp_plugin_dir );

// plugin url
$wp_plugin_url = substr(plugin_dir_url(__FILE__), 0, -1);
define('LCEPS_URL', $wp_plugin_url );

// multilang key
define('LCEPS_ML', 'lceps_ml');




/////////////////////////////////////////////
/////// MULTILANGUAGE SUPPORT ///////////////
/////////////////////////////////////////////

function lceps_multilanguage() {
    $param_array = explode('/', LCEPS_DIR);
    $folder_name = end($param_array);

    load_plugin_textdomain(LCEPS_ML, false, $folder_name .'/languages');
}
add_action('init', 'lceps_multilanguage', 1);




////////////////////////////////////////////////////////




// SCRIPTS AND CSS ENQUEUING
function lceps_cripts() { 
    wp_enqueue_style('lceps_admin', LCEPS_URL .'/css/admin.css', 100);
    
    wp_enqueue_style('lceps_chosen', LCEPS_URL .'/js/chosen/chosen.min.css');
    wp_enqueue_script('lceps_chosen', LCEPS_URL .'/js/chosen/chosen.jquery.min.js', 800, '1.0', true);
    
    wp_enqueue_script('lceps_emoji-picker', LCEPS_URL .'/js/lc-emoji-picker/lc_emoji_picker.min.js', 800, '1.0', true);
}
add_action('admin_enqueue_scripts', 'lceps_cripts');




////////////////////////////////////////////////////////



include_once(LCEPS_DIR . '/helpers.php');

include_once(LCEPS_DIR . '/admin_menu.php');

include_once(LCEPS_DIR . '/classes/tokens_manager.php');

include_once(LCEPS_DIR . '/cpt_fields.php');

include_once(LCEPS_DIR . '/admin_ajax.php');

include_once(LCEPS_DIR . '/front_endpoints.php');



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////




// setup database on activation

function lceps_on_activation() {
    include_once(LCEPS_DIR . '/db_manag.php');
}
register_activation_hook(__FILE__, 'lceps_on_activation');
