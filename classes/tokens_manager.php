<?php

class lceps_tokens_manager {
    
    private $table_name = false;
    private $db = false;
    
    
    public function __construct() {
        global $wpdb;
        
        $this->table_name = (is_plugin_active_for_network('lc_expo_push_notifications/lc_expo_push_notifications.php')) ? $wpdb->base_prefix . "lceps_tokens" : $wpdb->prefix . "lceps_tokens";  
        $this->db = $wpdb;
    }
    
    
    
    public function get_tokens_count() {
        return (int)$this->db->get_var("SELECT COUNT(*) FROM ". $this->table_name);
    }
           
    
    
    /* get tokens list as array */
    public function get_tokens() {
        return (array)$this->db->get_col("SELECT token FROM ". $this->table_name);
    }
    
    
    
    /* know whether a token is in the database - returns bool */
    public function token_exists($token) {
        $result = (int)$this->db->get_var("SELECT COUNT(*) FROM ". $this->table_name ." WHERE token = '". esc_html(addslashes($token)) ."'");
        return ($result) ? true : false;
    }
    
    
    
    /* add token to database, returns bool */
    public function add_token($token) {
        if(!trim($token)) {
            return false;    
        }
        
        return $this->db->insert( 
			$this->table_name, 
			array( 
				'token' => $token
			)
		);
    }
    
    
    
    /* remove token to database, returns bool */
    public function remove_token($token) {
        if(!trim($token)) {
            return false;    
        }
        
        return $this->db->delete(
            $this->table_name, 
            array('token' => $token)
        );
    }
    
    
    
    /* empty the database, returns bool */
    public function empty_the_db() {
        return $this->db->query('TRUNCATE TABLE '.$this->table_name);
    }
}


$GLOBALS['lceps_tokens_manager'] = new lceps_tokens_manager;