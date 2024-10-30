<?php
// declaring menu, custom post type and taxonomy


///////////////////////////////////
// SETTINGS PAGE

function lceps_settings_page() {	
	add_submenu_page('edit.php?post_type=lceps_notifications', __('Settings', LCEPS_ML), __('Settings', LCEPS_ML), 'install_plugins', 'lceps_settings', 'lceps_settings');	
}
add_action('admin_menu', 'lceps_settings_page');


function lceps_settings() {
	include_once(LCEPS_DIR . '/settings/view.php');
}



// rename first submenu
function lceps_submenu_rename() {
    global $submenu;
    $submenu['edit.php?post_type=lceps_notifications'][5][0] = __('Notifications', LCEPS_ML);
}
add_action('admin_menu', 'lceps_submenu_rename');





///////////////////////////////////////
// CUSTOM POST TYPE & TAXONOMY

add_action( 'init', 'register_lceps_notifications_cpt');
function register_lceps_notifications_cpt() {

    $labels = array( 
        'name' => __('Notifications', LCEPS_ML),
        'singular_name' => __( 'Notification', LCEPS_ML),
        'add_new' => __( 'Add New Notification', LCEPS_ML),
        'add_new_item' => __( 'Add New Notification', LCEPS_ML),
        'edit_item' => __( 'Edit Notification', LCEPS_ML),
        'new_item' => __( 'New Notification', LCEPS_ML),
        'view_item' => __( 'View Notification', LCEPS_ML),
        'search_items' => __( 'Search Notifications', LCEPS_ML),
        'not_found' => __( 'No notifications found', 'lceps_galleries' ),
        'not_found_in_trash' => __( 'No notifications found in Trash', LCEPS_ML),
        'parent_item_colon' => __( 'Parent Notification:', LCEPS_ML),
        'menu_name' => __('LC Expo Push Notifications', LCEPS_ML),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array('title'), 
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
		'menu_icon' => 'dashicons-megaphone',
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'page'
    );
    register_post_type('lceps_notifications', $args);
    
    //////
	
	$labels = array( 
        'name' => __('Notification Categories', LCEPS_ML),
        'singular_name' => __( 'Notification Category', LCEPS_ML),
        'search_item' => __( 'Search Notification Categories', LCEPS_ML),
        'popular_items' => NULL,
        'all_items' => __( 'All Notification Categories', LCEPS_ML),
        'parent_item' => __( 'Parent Notification Category', LCEPS_ML),
        'parent_item_colon' => __( 'Parent Notification Category:', LCEPS_ML),
        'edit_item' => __( 'Edit Notification Category', LCEPS_ML),
        'update_item' => __( 'Update Notification Category', LCEPS_ML),
        'add_new_item' => __( 'Add New Notification Category', LCEPS_ML),
        'new_item_name' => __( 'New Notification Category', LCEPS_ML),
        'separate_items_with_commas' => __( 'Separate notification categories with commas', LCEPS_ML),
        'add_or_remove_items' => __( 'Add or remove notification Categories', LCEPS_ML),
        'choose_from_most_used' => __( 'Choose from most used notification categories', LCEPS_ML),
        'menu_name' => __( 'Notification Categories', LCEPS_ML),
    );

    $args = array( 
        'labels' 			=> $labels,
        'public' 			=> false,
        'show_in_nav_menus' => false,
        'show_ui' 			=> true,
        'show_tagcloud'		=> false,
        'hierarchical' 		=> true,
        'rewrite' 			=> false,
        'query_var' 		=> true,
		'update_count_callback' => '_update_generic_term_count'
    );
    register_taxonomy('lceps_notif_cat', 'lceps_notifications', $args);
}




// disable autosave
function lceps_disable_cpt_autosave() {
    global $post;
    
    if(get_post_type($post->ID) === 'lceps_notifications'){
        wp_deregister_script('autosave');
    }
}
add_action('admin_print_scripts', 'lceps_disable_cpt_autosave');






//////////////////////////////
// VIEW CUSTOMIZATORS

function lceps_updated_messages( $messages ) {
  global $post;

  $messages['lceps_notifications'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => __('Notification updated', LCEPS_ML),
    2 => __('Notification updated', LCEPS_ML),
    3 => __('Notification deleted', LCEPS_ML),
    4 => __('Notification updated', LCEPS_ML),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Notification restored to revision from %s', LCEPS_ML), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => __('Notification published', LCEPS_ML),
    7 => __('Notification saved', LCEPS_ML),
    8 => __('Notification submitted', LCEPS_ML),
    9 => sprintf( __('Notification scheduled for: <strong>%1$s</strong>', LCEPS_ML), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ))),
    10 => __('Notification draft updated', LCEPS_ML),
  );

  return $messages;
}
add_filter('post_updated_messages', 'lceps_updated_messages');





// customize galleries CPT table
function lceps_edit_pt_table_head($columns) {
	$new_cols = array();
	
	$new_cols['cb']            = '<input type="checkbox" />';
	$new_cols['lceps_id']           = 'ID';
	$new_cols['title']         = __('Title', 'column name');
	$new_cols['lceps_text']    = __('Text', LCEPS_ML);
    $new_cols['lceps_cat']    = __('Categories', LCEPS_ML);
	$new_cols['date'] 		   = __('Date', 'column name');
	
	return $new_cols;
}
add_filter('manage_edit-lceps_notifications_columns', 'lceps_edit_pt_table_head', 10, 2);



function lceps_edit_pt_table_body($column_name, $id) {

	switch ($column_name) {
		
        case 'lceps_id' : 
            echo '#'. $id;
			break;
            
        case 'lceps_text' : 	
			echo get_post_field('post_content', $id);
			break;
            
        case 'lceps_cat' :
			$cats = get_the_terms($id, 'lceps_notif_cat');
            if (is_array($cats)) {
				$item_cats = array();
				foreach($cats as $cat) {
                    $item_cats[] = $cat->name;
                }
                
				echo implode(', ', $item_cats);
			}
			else {
                echo '';
            }
			break;
		
		default:
			break;
	}
	return true;
}
add_action('manage_lceps_notifications_posts_custom_column', 'lceps_edit_pt_table_body', 10, 2);






/////////////////////////////////////
// ENABLE CPT FILTER BY TAXONOMY

function lceps_cpt_filter_by_cat() {
    global $typenow;
    global $wp_query;
	
    if ($typenow == 'lceps_notifications') {
        $taxonomy = 'lceps_notif_cat';
		
        $args = array(
            'taxonomy'      =>  $taxonomy,
            'hide_empty'    => true,
        );
        if(!wp_count_terms($args)) {
            return false;    
        }
        
		$sel = (isset($wp_query->query[$taxonomy])) ? $wp_query->query[$taxonomy] : ''; 
		
        wp_dropdown_categories(array(
            'show_option_all' =>  __("Any category", LCEPS_ML),
            'taxonomy'        =>  $taxonomy,
            'name'            =>  $taxonomy,
            'orderby'         =>  'name',
            'selected'        =>  $sel,
            'hierarchical'    =>  false,
            'depth'           =>  1,
            'show_count'      =>  false,
            'hide_empty'      =>  true
        ));
    }
}
add_action('restrict_manage_posts','lceps_cpt_filter_by_cat');



function lceps_cat_id_to_cat_term($query) {
	global $pagenow;
	
	$post_type = 'lceps_notifications';
	$taxonomy  = 'lceps_notif_cat';
	
	$q_vars    = &$query->query_vars;
	if($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy]) {
		
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}
add_filter('parse_query', 'lceps_cat_id_to_cat_term', 999);