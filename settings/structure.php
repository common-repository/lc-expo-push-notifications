<?php 
include_once(LCEPS_DIR . '/settings/field_options.php'); 
$ml_key = LCEPS_ML;




// FILTER - manipulate settings tabs
$tabs = array(
	'main_opts' 	=> __('Main Options', $ml_key),
);
$GLOBALS['lceps_settings_tabs'] = apply_filters('lceps_settings_tabs', $tabs);	



$wp_pag_templates = (function_exists('get_page_templates')) ? array_flip(get_page_templates()) : array();



// STRUCTURE
/* tabs index => array( 
	'sect_id' => array(
		'sect_name'	=> name
		'fields'	=> array(
			...
		)
	)
   )
*/

$structure = array();


####################################
########## MAIN OPTIONS ############
####################################
$structure['main_opts'] = array(
	
	'statistics' => array(
		'sect_name'	=>  '',
		'fields' 	=> array(
            
            'lceps_stats' => array(
				'type'		=> 'custom',
				'callback'	=> 'lceps_stats',
			), 
        ),
    ),
        
    
    'endpoints' => array(
        'sect_name'	=>  __('Endpoints', $ml_key),
            'fields' 	=> array(   
			
            'lceps_conjob_endpoint' => array(
				'label' 	=> __('Cronjob endpoint', $ml_key),
				'type'		=> 'label_message',
				'content'	=> '
                    <em><span class="lceps_ep_base">'. lceps_get_endpoint_base() . '</span>&send_notification</em>
                    <span class="dashicons dashicons-admin-page lceps_copy_cj_ep" title="'. esc_html__('copy to clipboard', $ml_key) .'"></span>
                    <span class="dashicons dashicons-external lceps_open_cj_ep" title="'. esc_html__('open in a new tab', $ml_key) .'"></span>
                    '
			),
			'lceps_register_token_endpoint' => array(
				'label' 	=> __('Register token endpoint', $ml_key),
				'type'		=> 'label_message',
				'content'	=> '<em><span class="lceps_ep_base">'. lceps_get_endpoint_base() . '</span>&add_token=THE-TOKEN</em>'
			),
            'lceps_remove_token_endpoint' => array(
				'label' 	=> __('Remove token endpoint', $ml_key),
				'type'		=> 'label_message',
				'content'	=> '<em><span class="lceps_ep_base">'. lceps_get_endpoint_base() . '</span>&remove_token=THE-TOKEN</em>'
			),
            'lceps_key_reset' => array(
				'type'		=> 'message',
				'content'	=> '<input type="button" class="button-secondary lceps_reset_key" value="'. esc_html__('Reset endpoint key', $ml_key) .'" />'
			),
        ),
    ),
    
    
    'extra_notif_params' => array(
		'sect_name'	=>  __('Notification parameters declaration', $ml_key) . '<input type="button" value="+ '. esc_html__('Add parameter', $ml_key) .'" class="button-secondary lceps_add_notif_param_declar" />',
		'fields' 	=> array(
            
            'lceps_stats' => array(
				'type'		=> 'custom',
				'callback'	=> 'lceps_extra_params',
			), 
        ),
    ),
    
        
    'extra' => array(
        'sect_name'	=>  __('Extra', $ml_key),
            'fields' 	=> array(   
			
            'lceps_analytics_id' => array(
				'label' => __('Google Analytics Tracking ID', $ml_key) .' <a href="https://support.google.com/analytics/answer/1008080#trackingID" target="_blank"><small>('. __('how to find it?', LCEPS_ML) .')</small></a>',
				'type'	=> 'text',
				'placeh'=> 'UA-XXXX-XX',
				'maxlen'	=> 20,
				'fullwidth' => true,
				'note'	=> __("Once saved, the plugin will create an event whenever a new token is added to the database", $ml_key)
			),
            'lceps_empty_db' => array(
				'type'		=> 'message',
				'content'	=> '<input type="button" class="button-secondary lceps_empty_db" value="'. esc_html__('Empty the database', $ml_key) .'" />'
			),
        ),
    ),
);




// FILTER - manipulate settings structure
$GLOBALS['lceps_settings_structure'] = apply_filters('lceps_settings_structure', $structure);
