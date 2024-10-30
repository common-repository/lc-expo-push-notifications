<?php

////////////////////////////////////////////////
////// RESET ENDPOINT KEY //////////////////////
////////////////////////////////////////////////

function lceps_reset_endpoint_key() {
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lcwp_ajax')) {
		wp_die(json_encode(array('status'=>'error', 'txt' => "Cheating?"))); 		
	};
	
    $new_key = uniqid();
    update_option('lceps_key', $new_key);
    
    wp_die(json_encode(array(
        'status'    => 'success', 
        'txt'       => lceps_get_endpoint_base(),
    ))); 
}
add_action('wp_ajax_lceps_reset_endpoint_key', 'lceps_reset_endpoint_key');





////////////////////////////////////////////////
////// EMPTY THE DATABASE //////////////////////
////////////////////////////////////////////////

function lceps_empty_db() {
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lcwp_ajax')) {
		wp_die("Cheating?"); 		
	};
    global $lceps_tokens_manager;
   
    if($lceps_tokens_manager->empty_the_db()) {
        wp_die('success'); 
    } else {
        wp_die(esc_html__('Error emptying the database', LCEPS_ML));     
    }
}
add_action('wp_ajax_lceps_empty_db', 'lceps_empty_db');





////////////////////////////////////////////////
////// DIRECTLY DELETE NOTIFICATION ////////////
////////////////////////////////////////////////

function lceps_del_notif() {
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lcwp_ajax')) {
		wp_die("Cheating?"); 		
	};
    if(!isset($_POST['post_id']) || !(int)$_POST['post_id']) {
        wp_die("Post ID missing");        
    }
    
    (!wp_delete_post((int)$_POST['post_id'], true)) ? wp_die(esc_html__('Error deleting the notification')) : wp_die('success');
}
add_action('wp_ajax_lceps_del_notif', 'lceps_del_notif');