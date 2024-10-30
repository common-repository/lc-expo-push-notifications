<?php
// MANAGING NOIFICATIONS POST TYPE FIELDS

// register boxes
function lceps_cpt_metaboxes() {
    add_meta_box('lceps_cpt_main_metaboxes', '&nbsp;', 'lceps_cpt_main_metaboxes', 'lceps_notifications', 'normal', 'default');
}
add_action('admin_init', 'lceps_cpt_metaboxes');




// html code
function lceps_cpt_main_metaboxes() {
	global $post;
    $post_id = $post->ID;

    wp_cache_flush(); // avoid old data in forms 
    $post = get_post($post->ID);
    
    $date_arr = explode(' ', $post->post_date);
    $time_arr = explode(':', $date_arr[1]);
    ?>
    <div>
        <textarea name="lceps_content" placeholder="<?php esc_html_e('Notification content', LCEPS_ML) ?>" autocomplete="off"><?php echo esc_html($post->post_content) ?></textarea>
    </div>
    <div>
        <label>
            <span class="dashicons dashicons-calendar-alt"></span> 
            <?php esc_html_e('Publication date and time', LCEPS_ML) ?>
        </label>
        <input type="date" name="lceps_pubdate" value="<?php echo $date_arr[0] ?>" min="<?php echo date_i18n('Y-m-d') ?>" autocomplete="off" />
        
        <label><?php esc_html_e('at') ?></label>
        <input type="number" name="lceps_pubdate_hrs" value="<?php echo (int)$time_arr[0] ?>" min="0" max="23" step="1" autocomplete="off" style="width: 60px;" /> : 
        <input type="number" name="lceps_pubdate_min" value="<?php echo (int)$time_arr[1] ?>" min="0" max="59" step="1" autocomplete="off" style="width: 60px;" />
    </div>

    <input type="hidden" name="lceps_meta_nonce" value="<?php echo wp_create_nonce('lcwp_nonce') ?>" />



    <?php if(!empty(get_option('lceps_notif_params', array()))) : ?>
    <div class="postbox-container lceps_extra_params_wrap" style="margin-top: 40px;">
        <div id="lceps_notif_attr_metabox" class="postbox" style="margin: 0;">
            <div class="postbox-header" style="display: block;">
                <h2 class="hndle ui-sortable-handle"><b><?php esc_html_e('Extra parameters', LCEPS_ML) ?></b></h2>
            </div>

            <div class="inside">
                <table class="widefat" style="border: none;">
                    <tbody>
                    <?php foreach((array)get_option('lceps_notif_params') as $key => $data) : ?>
                        <tr>
                            <td><label><?php echo $data['name'] ?></label></td>
                            <td>
                                <?php
                                $f_name = 'lceps_'. sanitize_title($key) .'_param';
                                $val = get_post_meta($post->ID, $f_name, true);
                                
                                if($data['type'] == 'text') {
                                    echo '<textarea name="'. $f_name .'" onkeyup="lceps_textAreaAdjust(this)">'. esc_textarea($val) .'</textarea>';    
                                }
                                else {
                                    $multiple = ($data['type'] == 'select') ? '' : 'multiple="multiple"';
                                    $multiple_name = ($multiple) ? '[]' : '';
                                   
                                    echo '<select name="'. $f_name . $multiple_name .'" autocomplete="off" '. $multiple .'>';
                                        
                                        if(!$multiple) {
                                            echo '<option value="">('. esc_html__('not set') .')</option>';    
                                        }
                                    
                                        foreach(preg_split('/\r\n|[\r\n]/', $data['val']) as $opt) {
                                            if(!is_array($val)) {
                                                $sel = selected(esc_attr($opt), $val, false);
                                            } else {
                                                $sel = (in_array(esc_attr($opt), $val)) ? 'selected="selected"' : '';  
                                            }
                                            
                                            echo '<option value="'. esc_attr($opt) .'" '. $sel .'>'. esc_html($opt) .'</option>';        
                                        }
                                    echo '</select>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
	       </div>
        </div>
    </div>
    <?php endif; ?>


    <script type="text/javascript">
    const has_been_submitted = <?php echo (get_post_meta($post->ID, 'lceps_notif_sent', true)) ? 'true' : 'false' ?>;
   
    // set status label
    jQuery('#misc-publishing-actions').append(`
    <div class="misc-pub-section misc-pub-post-status lceps-post-status">
        <?php esc_html_e('Status') ?>: <span id="post-status-display"><?php (empty($post->post_excerpt)) ? esc_html_e('Scheduled') : esc_html_e('Published') ?></span>
    </div>`);



    // disable everything if status == publish == notification sent
    if(has_been_submitted) {
        jQuery('.wrap').find('input, select, textarea, button').prop("disabled", "disabled");    
        jQuery('#publish, .edit-post-status').remove();

        jQuery('#post').before('<div id="message" class="updated notice notice-success"><p><?php esc_html_e('Notification sent', LCEPS_ML) ?></p></div>');
    }



    // add link to delete notification
    jQuery('#delete-action').append('<a class="submitdelete lceps_del" href="javascript:void(0)"><?php esc_html_e('Delete', LCEPS_ML) ?></a>');
        
        
        
    // on doc's ready    
    jQuery(document).ready(function($) {

        // delete
        $(document).on('click', '#delete-action .lceps_del', function(e) {
            if(!confirm("<?php esc_html_e('Do you really want to definitively delete the notification?', LCEPS_ML) ?>")) {
                return false;    
            }
            $('.spinner').css('visibility', 'visible');
            
            // submit
            var data = {
                action	: 'lceps_del_notif',
                post_id : <?php echo $post_id ?>,
                nonce	: '<?php echo wp_create_nonce('lcwp_ajax') ?>',
            };
            $.post(ajaxurl, data, function(response) {
                if($.trim(response) == 'success') {
                    toast_message('success', "<?php esc_html_e('Notification successfully deleted!', LCEPS_ML) ?>");
                    
                    setTimeout(function() {
                        window.location.href = '<?php echo site_url() ?>/wp-admin/edit.php?post_type=lceps_notifications';    
                    }, 1500);
                }
                else {
                    toast_message('error', response);
                }
            })
            .fail(function() {
                toast_message('error', "<?php esc_html_e('Error deleting the notification', LCEPS_ML) ?>");    
            })
            .always(function() {
                $('.spinner').css('visibility', 'hidden'); 
            }); 
        });
        
        
        
        // validate on submission
        $('#publish, #save-post').on('click', function() {
            
            if(has_been_submitted) {
                toast_message('error', "<?php _e("You can't edit an already submitted notification", LCEPS_ML) ?>");
                return false;    
            }
            else if(!$.trim($('#title').val())) {
                toast_message('error', "<?php _e("Please insert a title", LCEPS_ML) ?>");
                return false;      
            }
            else if($.trim($('#title').val()).length > 150 ) {
                toast_message('error', "<?php _e("Title too long", LCEPS_ML) ?>");
                return false;          
            }
            else if(!$.trim($('textarea[name="lceps_content"]').val())) {
                toast_message('error', "<?php _e("Please insert some content", LCEPS_ML) ?>");
                return false;      
            }
            else if($.trim($('textarea[name="lceps_content"]').val()).length > 250 ) {
                toast_message('error', "<?php _e("Contents too long", LCEPS_ML) ?>");
                return false;          
            }
            
            
            let hrs = parseInt($('input[name="lceps_pubdate_hrs"]').val(), 10);
            if(hrs.toString().length < 2) {hrs = '0'+hrs;}
            
            let min = parseInt($('input[name="lceps_pubdate_min"]').val(), 10);
            if(min.toString().length < 2) {min = '0'+min;}
            
            let date = $.trim($('input[name="lceps_pubdate"]').val()) +' '+ hrs +':'+ min +':00';
            date = new Date(Date.parse(date));
  
            let now = new Date();
            if(date.getTime() < now.getTime()) {
                toast_message('error', "<?php _e("Please use a future date", LCEPS_ML) ?>");
                return false;              
            }
            
            return true;
        });
        
        
        
        
        // emoji picker
        new lc_emoji_picker('textarea[name="lceps_content"]', {
            trigger_position    : {
                top : '5px',
                right: '7px',
            },
            target_r_padding    : 27, 
            emoji_json_url      : '<?php echo LCEPS_URL ?>/js/lc-emoji-picker/emoji-list.min.json',
        });
        
        
        
        // auto-heghht textarea
        window.lceps_textAreaAdjust = function(o) {
            o.style.height = "1px";
            o.style.height = (4 + o.scrollHeight)+"px";
        };
        $('.lceps_extra_params_wrap textarea').each(function() {
            lceps_textAreaAdjust(this);    
        });
        
        
        
        // chosen
        $('.lceps_extra_params_wrap select').chosen({width: '90%'});
        
        
        
        // toast message
        var toast_message = function(type, text) {
            if(!$('#lc_toast_mess').length) {
                $('body').append('<div id="lc_toast_mess"></div>');

                $('head').append(
                '<style type="text/css">' +
                '#lc_toast_mess,#lc_toast_mess *{-moz-box-sizing:border-box;box-sizing:border-box}#lc_toast_mess{background:rgba(20,20,20,.2);position:fixed;top:0;right:-9999px;width:100%;height:100%;margin:auto;z-index:9999999999999;opacity:0;filter:alpha(opacity=0);-webkit-transition:opacity .15s ease-in-out .05s,right 0s linear .5s;-ms-transition:opacity .15s ease-in-out .05s,right 0s linear .5s;transition:opacity .15s ease-in-out .05s,right 0s linear .5s}#lc_toast_mess.lc_tm_shown{opacity:1;filter:alpha(opacity=100);right:0;-webkit-transition:opacity .3s ease-in-out 0s,right 0s linear 0s;-ms-transition:opacity .3s ease-in-out 0s,right 0s linear 0s;transition:opacity .3s ease-in-out 0s,right 0s linear 0s}#lc_toast_mess:before{content:"";display:inline-block;height:100%;vertical-align:middle}#lc_toast_mess>div{position:relative;padding:13px 16px!important;border-radius:2px;box-shadow:0 2px 17px rgba(20,20,20,.25);display:inline-block;width:310px;margin:0 0 0 50%!important;left:-155px;top:-13px;-webkit-transition:top .2s linear 0s;-ms-transition:top .2s linear 0s;transition:top .2s linear 0s}#lc_toast_mess.lc_tm_shown>div{top:0;-webkit-transition:top .15s linear .1s;-ms-transition:top .15s linear .1s;transition:top .15s linear .1s}#lc_toast_mess>div>span:after{font-family:dashicons;background:#fff;border-radius:50%;color:#d1d1d1;content:"";cursor:pointer;font-size:23px;height:15px;padding:5px 9px 7px 2px;position:absolute;right:-7px;top:-7px;width:15px}#lc_toast_mess>div:hover>span:after{color:#bbb}#lc_toast_mess .lc_error{background:#fff;border-left:4px solid #dd3d36}#lc_toast_mess .lc_success{background:#fff;border-left:4px solid #7ad03a}#lc_toast_mess .lc_modal {background: #fff;}#lc_toast_mess .lc_modal > span {display: none;}@media screen and (max-width:625px){#lc_toast_mess:before{height:135px}}' +
                '</style>');	

                // close toast message
                $(document.body).off('click tap', '#lc_toast_mess');
                $(document.body).on('click tap', '#lc_toast_mess', function() {
                    if(!$(this).find('.lc_modal').length) {
                        $('#lc_toast_mess').removeClass('lc_tm_shown');
                    }
                });
            }

            // setup
            if(type == 'error') {
                $('#lc_toast_mess').empty().html('<div class="lc_error"><p>'+ text +'</p><span></span></div>');	
            } 
            else if(type == 'modal') {
                $('#lc_toast_mess').empty().html('<div class="lc_modal"><p>'+ text +'</p><span></span></div>');	
            } 
            else {
                $('#lc_toast_mess').empty().html('<div class="lc_success"><p>'+ text +'</p><span></span></div>');	

                setTimeout(function() {
                    $('#lc_toast_mess.lc_tm_shown span').trigger('click');
                }, 2150);	
            }

            // use a micro delay to let CSS animations act
            setTimeout(function() {
                $('#lc_toast_mess').addClass('lc_tm_shown');
            }, 30);	
        }
    });
    </script>
    <?php
}







// save data
function lceps_cpt_meta_save($post_id) {
    if(!isset($_POST['lceps_meta_nonce']) || !wp_verify_nonce($_POST['lceps_meta_nonce'], 'lcwp_nonce')) {
        return;
    }

    // sanitize and compose date
    $hrs = (int)$_POST['lceps_pubdate_hrs'];
    if($hrs < 0 || $hrs > 23) {
        $hrs = '00';    
    }
    if(strlen($hrs) < 2) {$hrs = '0'.$hrs;}

    $min = (int)$_POST['lceps_pubdate_min'];
    if($min < 0 || $min > 59) {
        $min = '00';    
    }
    if(strlen($hrs) < 2) {$hrs = '0'.$hrs;}


    if(!validate_iso_date($_POST['lceps_pubdate'])) {
        $_POST['lceps_pubdate'] = date("Y-m-d", strtotime("+1 days")); // use the tomorrow date in case of errors 
    }
    $date = $_POST['lceps_pubdate'] .' '. $hrs .':'. $min .':00';


    // must use WPBD since date is not set for drafts through wp_update_post()
    global $wpdb;
    $data = array(
        'post_content'  => sanitize_textarea_field(strip_tags($_POST['lceps_content'])),
        'post_date'     => $date,
        'post_date_gmt' => get_gmt_from_date($date),
    ); 

    $response = $wpdb->update($wpdb->posts, $data, array('ID' => $post_id));
    
      
    // update extra params
    if(!empty(get_option('lceps_notif_params', array()))) {
        foreach((array)get_option('lceps_notif_params') as $key => $data) {
            
            $f_name = 'lceps_'. sanitize_title($key) .'_param';
            $val = (isset($_POST[$f_name]) && !empty($_POST[$f_name])) ? sanitize_textarea_field($_POST[$f_name]) : false;
            
            ($val !== false) ? update_post_meta($post_id, $f_name, $val) : delete_post_meta($post_id, $f_name);   
        }
    }

    return $post_id;
}
add_action('save_post', 'lceps_cpt_meta_save', 9999);
