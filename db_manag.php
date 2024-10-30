<?php
// DATABASE TABLE CREATION AND MAINTENANCE
//// ACTIONS PERFORMED ON PLUGINS ACTIVATION

include_once(ABSPATH . 'wp-admin/includes/upgrade.php');
global $wpdb;


// main vars to perform actions
$db_version = 1.01;
$curr_vers = (float)get_option('lceps_db_version', 0);
$TABLE_NAME = (is_plugin_active_for_network('lc_expo_push_notifications/lc_expo_push_notifications.php')) ? $wpdb->base_prefix . "lceps_tokens" : $wpdb->prefix . "lceps_tokens";




/*** check for table existence and eventually create/update ***/
$wpdb->query("SHOW TABLES LIKE '". $TABLE_NAME ."'");

// add or update DB table
if(!$wpdb->num_rows || !$curr_vers || $curr_vers < $db_version || isset($_GET['pcfm_db_check'])) {
	$sql = "
	CREATE TABLE ". $TABLE_NAME ." (
		token VARCHAR(50) DEFAULT '' NOT NULL,".
		
		"
		PRIMARY KEY (token)
	) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";	
    
	dbDelta($sql);
}



update_option('lceps_db_version', $db_version);