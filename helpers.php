<?php

function lceps_get_endpoint_base() {
    
    // if no key stored - create one
    if(!get_option('lceps_key')) {
        update_option('lceps_key', uniqid());
    }
    
    $conjunction = (strpos(site_url(), '?') !== false) ? '&' : '?';
    return site_url() . $conjunction . 'lceps_key='. get_option('lceps_key');
}





// yyyy-mm-dd date validation, returns bool
function validate_iso_date($index_val) {
    $date = preg_split( '/[-\.\/ ]/', trim($index_val));
				
    $not_int = true;
    foreach($date as $date_part) {
        if(preg_match('/[\D]/', $date_part)) {
            $not_int = false; 
            break;
        }	
    }

    if(!$not_int || count($date) != 3 || !checkdate($date[1], $date[2], $date[0])) {
        return false;
    }	
    else {
        return true;
    }
}




// triggers "die()" associating the HTTP status
function lceps_die($message, $status_code) {
    http_response_code((int)$status_code);
    die($message);
}