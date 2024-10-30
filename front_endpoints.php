<?php
// FRONTEND HANDLERS FOR VARIOUS ENDPOINTS


add_action('template_redirect', 'lceps_front_enpoints', 1);

function lceps_front_enpoints() {
    if(!isset($_REQUEST['lceps_key'])) {
        return true;    
    }
    
    // check key
    if($_REQUEST['lceps_key'] != get_option('lceps_key')) {
        lceps_die('Wrong key', 400);    
    }
    
    
    include_once(LCEPS_DIR .'/classes/AnalyticsEvent.php'); 
    global $lceps_tokens_manager;
    
    
    
    // ADD TOKEN TO DATABASE
    if(isset($_REQUEST['add_token'])) {
        $token = sanitize_text_field($_REQUEST['add_token']);
        
        if(strpos($token, 'ExponentPushToken') === false || strlen($token) < 20 || strlen($token) > 50) {
            lceps_die('Wrong token', 400);       
        }
        else if($lceps_tokens_manager->token_exists($token)) {
            lceps_die('Token already in the database', 200);   
        }
        elseif(!$lceps_tokens_manager->add_token($token)) {
            lceps_die('Error adding token to the database', 400);    
        }
        else {
            if(get_option('lceps_analytics_id')) {
                $events = new AnalyticsEvent(get_option('lceps_analytics_id'), site_url());
                $events->trackEvent('LC Expo Push Notifications', 'tokens', 'token added');
            }
            
            lceps_die('Token successfully added!', 200);   
        }
    }
    
    
    
    
    // REMOVE TOKEN FROM DATABASE
    else if(isset($_REQUEST['remove_token'])) {
        $token = sanitize_text_field($_REQUEST['remove_token']);
        
        if(strpos($token, 'ExponentPushToken') === false || strlen($token) < 20 || strlen($token) > 50) {
            lceps_die('Wrong token');        
        }
        else if(!$lceps_tokens_manager->token_exists($token)) {
            lceps_die('Token not in the database', 400);    
        }
        elseif(!$lceps_tokens_manager->remove_token($token)) {
            lceps_die('Error removing token from the database', 400);   
        }
        else {
            if(get_option('lceps_analytics_id')) {
                $events = new AnalyticsEvent(get_option('lceps_analytics_id'), site_url());
                $events->trackEvent('LC Expo Push Notifications', 'tokens', 'token removed');
            }
            
            lceps_die('Token successfully removed!', 200);  
        }
    }
    
    
    
    
    // SEND NOTIFICATION
    else if(isset($_REQUEST['send_notification'])) {
        
        // get only one notification per call!    
        $args = array(
            'post_type' 		=> 'lceps_notifications', 
            'post_status' 		=> array('publish', 'future'),
            'posts_per_page'    => 1,
            'orderby'           => 'post_date',
            'order'             => 'ASC',
            
            'meta_query'        => array(
                array(
                 'key'      => 'lceps_notif_sent',
                 'compare'  => 'NOT EXISTS',
                ),
            ),
        );
        
        $query 	= new WP_Query($args);
        $notif	= (array)$query->posts;
        
        if(empty($notif) || $notif[0]->post_date_gmt > current_time('Y-m-d H:i:s', true)) {
            lceps_die('No notifications to be sent found ..', 204);        
        }
        $notif = $notif[0];
        
        
        // get tokens list
        $tokens = $lceps_tokens_manager->get_tokens();
        if(empty($tokens)) {
            lceps_die('No tokens found ..', 204);     
        }

        
        // prepare data
        $data = array(
            'to' => $tokens,
            'sound' => 'default',
            'title' => $notif->post_title,
            'body' => $notif->post_content,
            'data' => array(),    
        );
        
        
        // additional params?
        if(!empty(get_option('lceps_notif_params'))) {
            foreach((array)get_option('lceps_notif_params') as $key => $foo) {
                
                $f_name = 'lceps_'. sanitize_title($key) .'_param';
                $val = get_post_meta($notif->ID, $f_name, true); 
                
                if(!empty($val)) {
                    $data['data'][$key] = $val;    
                }
            }
        }
        
        // send
        $response = wp_remote_retrieve_body(wp_remote_post('https://exp.host/--/api/v2/push/send', array(
            'headers'   => array(
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'      => json_encode($data),
            'method'    => 'POST'
        )));

        // update notification status
        update_post_meta($notif->ID, 'lceps_notif_sent', 1);
        lceps_die('Notification #'. $notif->ID .' sent to '. count($tokens) .' tokens', 200);
    }
}