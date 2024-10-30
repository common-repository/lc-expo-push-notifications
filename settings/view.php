<?php 
$ml_key     = LCEPS_ML;
$prefix     = 'lceps'; 
$basedir    = LCEPS_DIR;
$baseurl    = LCEPS_URL;



// framework engine
include_once($basedir . '/classes/simple_form_validator.php');
include_once($basedir . '/settings/settings_engine.php'); 
include_once($basedir . '/settings/field_options.php'); 
include_once($basedir . '/settings/custom_fields.php');
include_once($basedir . '/settings/structure.php'); 
?>

<div class="wrap lcwp_settings_wrap" style="direction: LTR; overflow-x: hidden;">  
    <h2 class="lceps_page_title"><?php _e('LC Expo Push Notifications Settings', $ml_key) ?></h2>  
    
    <style>
    input[name=lceps_analytics_id] {
        max-width: 150px;    
    }
    </style>
    
	<?php
    $engine = new lceps_settings_engine($prefix.'_settings', $GLOBALS[$prefix.'_settings_tabs'], $GLOBALS[$prefix.'_settings_structure']);
    
    // get fetched data and allow customizations
    if($engine->form_submitted()) {
        $fdata = $engine->form_data;
        $errors = (!empty($engine->errors)) ? $engine->errors : array();


		///////////////
		// CUSTOM ACTIONS PRE SAVE
        
        // validate parameters data
        $params_key     = (array)$_POST['lceps_npd_key'];
        $params_type    = (array)$_POST['lceps_npd_type'];
        $params_name    = (array)$_POST['lceps_npd_name'];
        $params_val     = (array)$_POST['lceps_npd_val'];
        
        $params_count   = count($params_type); 
        $notif_params   = array();
        
        if(count($params_key) !== $params_count || count($params_name) !== $params_count || count($params_val) !== $params_count) {
            $errors[ esc_html__("Notification parameters", LCEPS_ML) ] = __("values don't match", LCEPS_ML);        
        }
        else {
            for($a=0; $a<count($params_key); $a++) {
                
                // validation
                if(empty($params_key[$a]) || isset($notif_params[ $params_key[$a] ])) {
                    $errors[ esc_html__("Notification parameters", LCEPS_ML) ] = __("one or more keys are empty or duplicate", LCEPS_ML);       
                    break;    
                }
                
                if(empty($params_name[$a]) || ($params_type[$a] != 'text' && empty($params_val[$a]))) {
                    $errors[ esc_html__("Notification parameters", LCEPS_ML) ] = __("one or more names or values are empty", LCEPS_ML);       
                    break;    
                }
                
                $notif_params[ sanitize_text_field($params_key[$a]) ] = array(
                    'type'  => sanitize_text_field($params_type[$a]),
                    'name'  => sanitize_text_field($params_name[$a]),
                    'val'   => sanitize_textarea_field($params_val[$a])
                );
            }
        }
        
		
		///////////////


        // FILTER - manipulate setting errors - passes errors array and form values - error subject as index + error text as val
        $errors = apply_filters($prefix.'_setting_errors', $errors, $fdata);	
        
        
        // save or print error
        if(empty($errors)) {
            
            // FR-FILTER - allow data manipulation (or custom actions) before settings save - passes form values
            $engine->form_data = apply_filters($prefix.'_before_save_settings', $fdata); 
            
            
            // save
            $engine->save_data();
			
			
			///////////////
			// CUSTOM ACTIONS AFTER SAVE
            
            update_option('lceps_notif_params', $notif_params);
			
			///////////////
			
			
			// refresh to allow saved values to be spread in structure
			if(!isset($noredirect)) {
				ob_end_clean(); // avoid issues with previously printed code
				wp_redirect( str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '&lcwp_sf_success');
				
  				exit;
			}
        }
        
        // compose and return errors
        else {
            $err_elems = array();
            foreach($errors as $i => $v) {
                if(is_numeric($i)) {
                    $err_elems[] = $v;	
                }
                else {
                    $err_elems[] = $i .' - '. $v;	
                }
            }
            
            echo '<div class="error lcwp_settings_result"><p>'. implode(' <br/> ', $err_elems) .'</p></div>';	
        }
    }
	
	
	// if successdully saved
	if(isset($_GET['lcwp_sf_success'])) {
		echo '<div class="updated lcwp_settings_result" style="display: none;"><p><strong>'. __('Options saved', $ml_key) .'</strong></p></div>';	
	}
	
	// print form code
    echo $engine->get_code();
    ?>
</div>



<?php // CUSTOM CSS ?>


<?php // SCRIPTS ?>
<script type="text/javascript" charset="utf8">
jQuery(document).ready(function($) {
	var nonce = '<?php echo wp_create_nonce('lcwp_ajax') ?>';
	
    
    // copy cronjob endpoint
    $(document).on('click', '.lceps_copy_cj_ep', function() {
        var $temp = $("<input>");
        $("body").append($temp);
        
        const url = $('.lceps_lceps_conjob_endpoint em').text();
        $temp.val(url).select();
        document.execCommand("copy");
        
        $temp.remove(); 
        toast_message('success', "<?php esc_html_e('URL copied to clipboard', LCEPS_ML) ?>");    
    }); 
    
    
    // open cronjob endpoint
    $(document).on('click', '.lceps_open_cj_ep', function() {
        window.open($('.lceps_lceps_conjob_endpoint em').text(), '_blank');
    }); 
    
    
    
    // auto-heghht textarea
    window.lceps_textAreaAdjust = function(o) {
        o.style.height = "1px";
        o.style.height = (4 + o.scrollHeight)+"px";
	};
    $('textarea[name="lceps_npd_val[]"]').each(function() {
        lceps_textAreaAdjust(this);    
    });
    
    
    
    // add notification parameter
    $(document).on('click', '.lceps_add_notif_param_declar', function(e) {
        const key = prompt("<?php esc_html_e("Please enter the new parameter key", LCEPS_ML) ?>").replace(/"/g,"");
        let duplicate = false;
        
        $('.lceps_npd_key input').each(function() {
            if($(this).val() == key) {
                duplicate = true;
                return false;
            }
        })
        
        if(duplicate) {
            toast_message('error', "<?php esc_html_e('Key already existing', LCEPS_ML) ?>");    
            return false;    
        }
        
        $('.extra_notif_params_table tbody').append(`
            <tr>
                <td class="lceps_npd_del"><span class="dashicons dashicons-no-alt" title="<?php esc_html_e("delete parameter", LCEPS_ML) ?>"></span></td>
                <td class="lceps_npd_key">
                    <input type="text" name="lceps_npd_key[]" value="${key}" autocomplete="off" maxlength="100" />
                </td>
                <td>
                    <select name="lceps_npd_type[]" autocomplete="off">
                        <option value="text"><?php esc_html_e("text") ?></option>
                        <option value="select"><?php esc_html_e("select", LCEPS_ML) ?></option>
                        <option value="multi-select"><?php esc_html_e("multi-select", LCEPS_ML) ?></option>
                    </select>
                </td>
                <td>
                    <input type="text" name="lceps_npd_name[]" value="" autocomplete="off" />
                </td>
                <td>
                    <textarea name="lceps_npd_val[]" autocomplete="off" onkeyup="lceps_textAreaAdjust(this)" style="display: none;"></textarea>
                </td>
            </tr>
        `);
    });
    
    
    // toggle param values field
    $(document).on('change', 'select[name="lceps_npd_type[]"]', function() {
        $(this).parents('tr').first().find('textarea[name="lceps_npd_val[]"]').toggle();    
    });
    
    
    // remove parameter
    $(document).on('click', '.lceps_npd_del span', function() {
        if(confirm("<?php esc_html_e("Do you really want to remove this parameter?", LCEPS_ML) ?>")) {
            $(this).parents('tr').first().remove();        
        }
    });
    
    
    
    //////////////////////////////////////////////////
    
    
    
    // reset security key
    $(document).on('click', '.lceps_reset_key:not(.lceps_acting)', function(e) {
        
        if(!confirm("<?php esc_html_e('WARNING: actual endpoints will stop working. Do you want to continue?', LCEPS_ML) ?>")) {
            return false;    
        }
        
        const $this = $(this);
        $this.addClass('lceps_acting').fadeTo(300, 0.6); 
        
        // submit
		var data = {
			action	: 'lceps_reset_endpoint_key',
			nonce	: nonce
		};
		$.post(ajaxurl, data, function(response) {
			const resp = JSON.parse(response);
			
			if(resp.status == 'success') {
				toast_message('success', "<?php esc_html_e('Key resetted successfully!', LCEPS_ML) ?>");
				$('.lceps_ep_base').text(resp.txt);
			}
			else {
				toast_message('error', resp.txt);
            }
		})
        .fail(function() {
            toast_message('error', "<?php esc_html_e('Error resetting the key', LCEPS_ML) ?>");    
        })
        .always(function() {
            $('.lceps_reset_key').removeClass('lceps_acting').fadeTo(300, 1);   
        });	 
    });
    
    

    // empty database
    $(document).on('click', '.lceps_empty_db:not(.lceps_acting)', function(e) {
        
        if(!confirm("<?php esc_html_e('WARNING: stored tokens will be totally deleted. Continue?', LCEPS_ML) ?>")) {
            return false;    
        }
        
        const $this = $(this);
        $this.addClass('lceps_acting').fadeTo(300, 0.6); 
        
        // submit
		var data = {
			action	: 'lceps_empty_db',
			nonce	: nonce
		};
		$.post(ajaxurl, data, function(response) {
			if($.trim(response) == 'success') {
				toast_message('success', "<?php esc_html_e('Database successfully emptied!', LCEPS_ML) ?>");
				$('#lceps_tokens_counter').text('0');
			}
			else {
				toast_message('error', response);
            }
		})
        .fail(function() {
            toast_message('error', "<?php esc_html_e('Error emptying the database', LCEPS_ML) ?>");    
        })
        .always(function() {
            $('.lceps_empty_db').removeClass('lceps_acting').fadeTo(300, 1);   
        });	 
    });
    
    
    
    
    ///////////////////////////////////////////////////////////////////
    
    
	
	// replacing jQuery UI tabs 
	jQuery('.lcwp_settings_tabs').each(function() {
    	var sel = '';
		var hash = window.location.hash;
		
		var $form = jQuery(".lcwp_settings_form");
		var form_act = $form.attr('action');

		// track URL on opening
		if(hash && jQuery(this).find('.nav-tab[href="'+ hash +'"]').length) {
			jQuery(this).find('.nav-tab').removeClass('nav-tab-active');
			jQuery(this).find('.nav-tab[href="'+ hash +'"]').addClass('nav-tab-active');	
			
			$form.attr('action', form_act + hash);
		}
		
		// if no active - set first as active
		if(!jQuery(this).find('.nav-tab-active').length) {
			jQuery(this).find('.nav-tab').first().addClass('nav-tab-active');	
		}
		
		// hide unselected
		jQuery(this).find('.nav-tab').each(function() {
            var id = jQuery(this).attr('href');
			
			if(jQuery(this).hasClass('nav-tab-active')) {
				sel = id
			}
			else {
				jQuery(id).hide();
			}
        });
		
		// scroll to top by default
		jQuery("html, body").animate({scrollTop: 0}, 0);
		
		// track clicks
		if(sel) {
			jQuery(this).find('.nav-tab').click(function(e) {
				e.preventDefault();
				if(jQuery(this).hasClass('nav-tab-active')) {return false;}
				
				var sel_id = jQuery(this).attr('href');
				window.location.hash = sel_id.replace('#', '');
				
				$form.attr('action', form_act + sel_id);
				
				// show selected and hide others
				jQuery(this).parents('.lcwp_settings_tabs').find('.nav-tab').each(function() {
                    var id = jQuery(this).attr('href');
					
					if(sel_id == id) {
						jQuery(this).addClass('nav-tab-active');
						jQuery(id).show();		
					}
					else {
						jQuery(this).removeClass('nav-tab-active');
						jQuery(id).hide();	
					}
                });
			});
		}
	});
   
  
   
	// sliders
	var lcwp_sf_slider_opt = function() {
		var a = 0; 
		$('.lcwp_sf_slider_wrap').each(function(idx, elm) {
			var sid = 'slider'+a;
			jQuery(this).attr('id', sid);	
		
			svalue = parseInt(jQuery("#"+sid).next('input').val());
			minv = parseInt(jQuery("#"+sid).attr('min'));
			maxv = parseInt(jQuery("#"+sid).attr('max'));
			stepv = parseInt(jQuery("#"+sid).attr('step'));
			
			jQuery('#' + sid).slider({
				range: "min",
				value: svalue,
				min: minv,
				max: maxv,
				step: stepv,
				slide: function(event, ui) {
					jQuery('#' + sid).next().val(ui.value);
				}
			});
			jQuery('#'+sid).next('input').change(function() {
				var val = parseInt(jQuery(this).val());
				var minv = parseInt(jQuery("#"+sid).attr('min'));
				var maxv = parseInt(jQuery("#"+sid).attr('max'));
				
				if(val <= maxv && val >= minv) {
					jQuery('#'+sid).slider('option', 'value', val);
				}
				else {
					if(val <= maxv) {jQuery('#'+sid).next('input').val(minv);}
					else {jQuery('#'+sid).next('input').val(maxv);}
				}
			});
			
			a = a + 1;
		});
	}
	lcwp_sf_slider_opt();
	
	
	
	
	//////////////////////////////////////////////////
	
	
	// fixed submit position
	var lcwp_sf_fixed_submit = function(btn_selector) {
		var $subj = jQuery(btn_selector);
		if(!$subj.length) {return false;}
		
		var clone = $subj.clone().wrap("<div />").parent().html();

		setInterval(function() {
			
			// if page has scrollers or scroll is far from bottom
			if((jQuery(document).height() > jQuery(window).height()) && (jQuery(document).height() - jQuery(window).height() - jQuery(window).scrollTop()) > 130) {
				if(!jQuery('.lcwp_settings_fixed_submit').length) {	
					$subj.after('<div class="lcwp_settings_fixed_submit">'+ clone +'</div>');
				}
			}
			else {
				if(jQuery('.lcwp_settings_fixed_submit').length) {	
					jQuery('.lcwp_settings_fixed_submit').remove();
				}
			}
		}, 50);
	};
	lcwp_sf_fixed_submit('.lcwp_settings_submit');
	
	
	
	//////////////////////////////////////////////////
	
	
	
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
	
	
	
	// on settings submit
	if(jQuery('.lcwp_settings_result').length) {
		
		var $subj = jQuery('.lcwp_settings_result');
		var subj_txt = $subj.find('p').html();
		
		// if success - simply hide main one
		if($subj.hasClass('updated')) {
			toast_message('success', subj_txt);	
			$subj.remove();	
			
			// remove &lcwp_sf_success
			history.replaceState(null, null, window.location.href.replace('&lcwp_sf_success', ''));
		}
		
		// show errors but keep them visible on top
		else {
			toast_message('error', "<?php _e('One or more errors occurred', $ml_key) ?>");
			jQuery("html, body").animate({scrollTop: 0}, 0);		
		}
	}
		
});
</script>



<?php
// ACTION - allow extra code printing in settings (for javascript/css)
do_action($prefix.'_settings_extra_code');
?>
