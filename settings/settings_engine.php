<?php
// TOOLSET TO DISPLAY AND MANAGE SETTINGS
// v1.3

class lceps_settings_engine {
	public $css_prefix 	= 'lceps_'; // prefix added to classes for customized styling
	public $ml_key 		= LCEPS_ML; // multilanguage key
	
	public $form_name	= '';
	public $tabs 		= array();
	public $structure	= array(); // multidimensional array composed by sections and innder fields
	
	protected $fields	= array(); // associative array wrapping fields data from $structure
	private $js_vis_cond= array(); // multidimensional array (field_id => condition's params) containing conditions to toggle fields. To be managed by js_vis_code()
	
	public $errors 			= ''; // form validation errors (HTML code)
	public $form_data 		= array(); // array containing form's data (associative array(field_name => value))
	
	
	
	/* INIT - setup tabs and filter */ 
	public function __construct($form_name, $tabs, $structure) {
		
		$this->form_name 	= $form_name;
		$this->tabs 		= $tabs;
		$this->structure 	= $structure;
		
		
		// store fields
		foreach($structure as $sections) {
			foreach($sections as $section) {
				foreach($section as $sid => $sval) {
					if($sid == 'fields') {
						foreach($sval as $field_id => $fvals) {
							$this->fields[$field_id] = $fvals;	
						}
					}
				}
			}
		}
		
		
		// form submitted - validate 
		if($this->form_submitted()) {
			$this->validate();
		}
		
		
		// form not submitted - fill $form_data with database
		else {

			// use validation indexes
			foreach($this->get_fields_validation() as $fv) {
				$fid = $fv['index'];
				
				$def = (isset($fields[$fid]['def'])) ? $fields[$fid]['def'] : false;
				$this->form_data[$fid] = get_option($fid, $def);
			}
		}
	}
	
	
	/* know if form has been submitted and "simple_form_validator" class exists - return bool */
	public function form_submitted() {
		return (class_exists('simple_fv') && isset($_POST[ $this->form_name ])) ? true : false;
	}
	


	/* print settings code (tabs + fields) */
	public function get_code() {
		$form_action = str_replace(array('%7E', '&lcwp_sf_success'), array('~', ''), $_SERVER['REQUEST_URI']);

		echo '<form name="'. $this->css_prefix .'settings" method="post" class="lcwp_settings_form '. $this->css_prefix .'settings_form form-wrap" action="'. $form_action .'">';
		
			// tabs
			echo $this->tabs_code();
			
			// sections and fields
			foreach($this->tabs as $tab_id => $tab_name) {
				if(!isset($this->structure[ $tab_id ])) {continue;}
				
				echo '<div id="'. $tab_id .'" class="lcwp_settings_block '. $this->css_prefix .'settings_block">';
				
				foreach($this->structure[ $tab_id ] as $sect_id => $section) {
					if(empty($section['fields'])) {continue;}
					
					echo '<h3 class="lcwp_settings_sect_title '. $this->css_prefix .'settings_sect_title" id="'. $sect_id .'">'. $section['sect_name'] .'</h3>';
					
					// init table only if has normal fields
					$use_table = false;
					foreach($section['fields'] as $field_id => $f) {
						if($f['type'] != 'custom') {
							$use_table = true;
							break;	
						}
					}
					
					if($use_table) {
						echo '<table class="widefat lcwp_settings_table '. $this->css_prefix .'settings_block"><tbody>';
					}
					
						// fields code
						foreach($section['fields'] as $field_id => $f) {
							$this->opt_to_code($field_id, $f);
						}
					
					if($use_table) {
						echo '</tbody></table>';
					}
				}
					
				echo '</div>';
			}
			
			
			// javascript visibility toggle code
			$this->js_vis_code();
			
			
			// nonce & submit button
			echo '
			<input type="hidden" name="'. $this->css_prefix .'settings_nonce" value="'. wp_create_nonce('lcwp') .'" /> 
			<input type="submit" name="'. $this->form_name .'" value="'. __('Update Options', $this->ml_key) .'" class="button-primary lcwp_settings_submit" />
			
		</form>';
	}
	
	
		
	/**************************************************************/
		
		
		
	/* tabs code */
	protected function tabs_code() {
		$code = '<h2 class="nav-tab-wrapper lcwp_settings_tabs '.$this->css_prefix.'settings_tabs">';
		
		foreach($this->tabs as $i => $v) {
			$code .= '<a class="nav-tab" href="#'. $i .'">'. $v .'</a>';		
		}
		return $code .'</h2>';
	}
		
		
		
	/* Passing field id and field's data array, returns its code basing on type */ 	
	public function opt_to_code($field_id, $field) {	
		$f = $field;
		
		// set field value
		if(!isset($this->form_data[$field_id])) {
			$val = '';	
		}
		else if(isset($this->form_data[$field_id]) && $this->form_data[$field_id] !== false || ($this->form_data[$field_id] === false && !isset($f['def']))) {
			$val = $this->form_data[$field_id];	
		} else {
			$val = (isset($f['def'])) ? $f['def'] : ''; 	
		}

		
		// CUSTOM FIELD - call external function
		if($f['type'] == 'custom') {
			
			// store js visibility params
			if(isset($f['js_vis'])) {
				$this->js_vis_cond[$field_id] = $f['js_vis'];	
			}
			
			return (function_exists($f['callback'])) ? call_user_func($f['callback'], $field_id, $f, $val, $this->form_data) : true;	
		}
		
		
		//////
		
		
		// SPACER
		if($f['type'] == 'spacer') {
			$tr_hidden = (isset($f['hide']) && $f['hide']) ? 'style="display: none;"' : '';
			echo '<tr class="'. $this->css_prefix . $field_id .'" '.$tr_hidden.'><td class="lcwp_sf_spacer" data-field-id="'. $field_id .'" colspan="3"></td></tr>';
			
			// store js visibility params
			if(isset($f['js_vis'])) {
				$this->js_vis_cond[$field_id] = $f['js_vis'];	
			}
			return true;
		}
		
		// MESSAGE
		if($f['type'] == 'message') {
			$tr_hidden = (isset($f['hide']) && $f['hide']) ? 'style="display: none;"' : '';
			echo '<tr class="'. $this->css_prefix . $field_id .'" '.$tr_hidden.'><td class="lcwp_sf_message" data-field-id="'. $field_id .'" colspan="3">'. $f['content'] .'</td></tr>';
			
			// store js visibility params
			if(isset($f['js_vis'])) {
				$this->js_vis_cond[$field_id] = $f['js_vis'];	
			}
			return true;
		}
		
		// LABEL + MESSAGE
		if($f['type'] == 'label_message') {
			$tr_hidden = (isset($f['hide']) && $f['hide']) ? 'style="display: none;"' : '';
			echo '
			<tr class="'. $this->css_prefix . $field_id .'" '.$tr_hidden.'>
				<td class="lcwp_sf_label"><label>'. $f['label'] .'</label></td>
				<td class="lcwp_sf_message" data-field-id="'. $field_id .'" colspan="2">'. $f['content'] .'</td>
			</tr>';
			
			// store js visibility params
			if(isset($f['js_vis'])) {
				$this->js_vis_cond[$field_id] = $f['js_vis'];	
			}
			return true;
		}
		
		
		//////
		
		$tr_hidden = (isset($f['hide']) && $f['hide']) ? 'style="display: none;"' : '';
		echo '<tr class="'. $this->css_prefix . $field_id .'" '.$tr_hidden.'>';
		
		
		// if code editor - fill whole space
		if($f['type'] == 'code_editor') {
			echo '<td class="lcwp_sf_field" colspan="3">'; 	
			$is_fullwidth = true;
		}
		else {
		
			// label block
			echo '<td class="lcwp_sf_label"><label>'. $f['label'] .'</label></td>';
			
			// field - start
			$is_fullwidth = ((isset($f['fullwidth']) && $f['fullwidth']) || $f['type'] == 'textarea' || $f['type'] == 'wp_editor') ? true : false;
			echo ($is_fullwidth) ? '<td class="lcwp_sf_field" colspan="2">' : '<td class="lcwp_sf_field">';
		}
		
		
		
		// switch by type
		switch($f['type']) {
			
			// text
			case 'text' :
				$ph = (isset($f['placeh'])) ? $f['placeh'] : ''; 
				$ml = (isset($f['max_val_len'])) ? 'maxlength="'. (int)$f['max_val_len'] .'"' : '';
				
				echo '<input type="text" name="'. $field_id .'" value="'. esc_attr((string)$val) .'" '.$ml.' placeholder="'. esc_attr($ph) .'" autocomplete="off" />';
				break;
				
			// password
			case 'psw' :
				
				// if value exists - show a message and an hidden field + system to change val
				if(!empty($val)) {
					echo '
                    <div class="lcwp_sf_edit_psw">
						<span>'. __('Password already set!', PC_ML) .'</span>
						<a href="javascript:void(0)" rel="'. $field_id .'" style="padding-left: 5px;" title="'. __('change password', PC_ML) .'"><span class="dashicons dashicons-edit"></span></a>
						<input type="hidden" name="'. $field_id .'" value="|||lcwp_sf_psw_placeh|||" />
					</div>';
					
					if(!isset($GLOBALS['lcwp_sf_edit_psw_js'])) {
						$GLOBALS['lcwp_sf_edit_psw_js'] = true;
						
						?>
                        <script type="text/javascript">
						jQuery(document).ready(function(e) {
                        	jQuery(document).delegate('.lcwp_sf_edit_psw a', 'click', function() {
								 jQuery(this).parents('.lcwp_sf_edit_psw').replaceWith('<input type="password" name="'+ jQuery(this).attr('rel') +'" value="" autocomplete="off" />');
							});  
                        });
						</script>
                        <?php	
					}
				}
				else {
					echo '<input type="password" name="'. $field_id .'" value="" autocomplete="off" />';
				}
					
				break;	
				
			// select
			case 'select' :
				$multiple_attr = (isset($f['multiple']) && $f['multiple']) ? 'multiple="multiple"' : '';
				$multiple_name = (isset($f['multiple']) && $f['multiple']) ? '[]' : '';
				
				echo '
				<select data-placeholder="'. __('Select an option', 'mg_ml') .' .." name="'. $field_id . $multiple_name.'" class="lcwp_sf_chosen" autocomplete="off" '.$multiple_attr.'>';
				
				foreach((array)$f['val'] as $key => $name) {
					if(isset($f['multiple']) && $f['multiple']) {
						$sel = (in_array($key, (array)$val)) ? 'selected="selected"' : '';
					} else {
						$sel = ($key == (string)$val) ? 'selected="selected"' : '';
					}
					
					echo '<option value="'.$key.'" '.$sel.'>'. $name .'</option>';	
				}
				
				echo '</select>';
				break;
			
			// checkbox
			case 'checkbox' :
				$sel = ($val) ? 'checked="checked"' : '';
				echo '
				<input type="checkbox" name="'. $field_id .'" value="1" class="lcwp_sf_check" '.$sel.' autocomplete="off" />';
				break;
			
			// textarea
			case 'textarea' :
				$ph = (isset($f['placeh'])) ? $f['placeh'] : ''; 
				echo '
				<textarea name="'. $field_id .'" placeholder="'. esc_attr($ph) .'" autocomplete="off">'. str_replace(array('<', '>'), array('&lt;', '&gt;'), (string)$val) .'</textarea>';
				break;
			
			// code editor
			case 'code_editor' :
				echo '
				<textarea id="'.$field_id.'" name="'. $field_id .'" autocomplete="off" class="lcwp_sf_code_editor" data-language="'. $f['language'] .'">'. str_replace(array('<', '>'), array('&lt;', '&gt;'), (string)$val) .'</textarea>';
				break;	
				
			// wp editor
			case 'wp_editor' :
				$args = array('textarea_rows'=> $f['rows']);
				wp_editor( (string)$val, $field_id, $args);
				break;	
			
			// slider
			case 'slider' :
				if(!isset($f['value'])) {$f['value'] = '';}
				
				echo '
				<div class="lcwp_sf_slider_wrap" step="'. $f['step'] .'" max="'.$f['max_val'].'" min="'.$f['min_val'].'"></div>
            	<input type="text" value="'. (int)$val .'" name="'. $field_id .'" maxlength="'. strlen($f['max_val']) .'" class="lcwp_sf_slider_input" autocomplete="off" />
                <span>'. $f['value'] .'</span>';
				break;
			
			// color
			case 'color' :
				echo '
				<div class="lcwp_sf_colpick">
                	<span class="lcwp_sf_colblock" style="background-color: '. $val .';"></span>
                	<input type="text" name="'. $field_id .'" value="'. $val .'" autocomplete="off" />
                </div>';
				break;
			
			// value and type
			case 'val_n_type' :
				echo '
				<input type="text" class="lcwp_sf_slider_input" name="'. $field_id .'" value="'. $val .'" maxlength="'. $f['max_val_len'] .'" style="height: 28px; width: 60px; display: inline-block; text-align: center;" autocomplete="off" />

				<select name="'. $field_id .'_type" style="height: 28px; position: relative; top: -3px; width: 60px;" autocomplete="off">';
					
					$sel = get_option($field_id .'_type');
					foreach($f['types'] as $i => $val) {
						echo '<option val="'.$i.'" '. selected($sel, $i) .'>'. $val .'</option>';	
					}

				echo '
				</select>';
				break;
			
			// 2 numbers
			case '2_numbers' :
				if(!is_array($val) || count($val) != 2) {$val = $f['def'];}
				
				$maxlen = 'maxlength="'. strlen($f['max_val']) .'"';
				$min = 'min="'. (int)$f['min_val'] .'"';
				$max = 'max="'. (int)$f['max_val'] .'"';
				
				for($a=0; $a<2; $a++) {
					echo '<input type="text" name="'. $field_id .'[]" value="'. (int)$val[$a] .'" '.$maxlen.' '.$min.' '.$max.' style="text-align: center; width: 60px; display: inline-block;" autocomplete="off" />' ;	
				}
				
				if(isset($f['value'])) {
					echo ' <span>'. $f['value'] .'</span>';
				}
				break;
				
			// 4 numbers
			case '4_numbers' :
				if(!is_array($val) || count($val) != 4) {$val = $f['def'];}
				
				$maxlen = 'maxlength="'. strlen($f['max_val']) .'"';
				$min = 'min="'. (int)$f['min_val'] .'"';
				$max = 'max="'. (int)$f['max_val'] .'"';
				
				for($a=0; $a<4; $a++) {
					echo '<input type="text" name="'. $field_id .'[]" value="'. (int)$val[$a] .'" '.$maxlen.' '.$min.' '.$max.' style="text-align: center; width: 54px; display: inline-block;" autocomplete="off" />' ;	
				}
				
				if(isset($f['value'])) {
					echo ' <span>'. $f['value'] .'</span>';
				}
				break;
		}
		
		
		
		// has note?
		$note = '';
		if(isset($f['note']) && $f['note']) {
			$note = ($is_fullwidth) ? '<p class="lcwp_sf_note">'. $f['note'] .'</p>' : '<span class="lcwp_sf_note">'. $f['note'] .'</span>';	
		}

		// fullwidth or textarea or wp editor 
		if($is_fullwidth) {
			echo '
				'. $note .'
			</td>';	
		}
		else {
			echo '
			</td>
			<td>'. $note .'</td>';	
		}
		
		echo '</tr>';
		
		
		// store js visibility params
		if(isset($f['js_vis'])) {
			$this->js_vis_cond[$field_id] = $f['js_vis'];	
		}
	}
		
		
		
	
	/* 
	 * Handling js_vis_cond data, prints javascript code to dynamically hide fields basing on other ones 
	 * 
	 * $this->js_vis_cond elements structure: 
	 	field_id => array(
		  'linked_field' 	=> (string) the field ID (name attr) to match,
		  'value'			=> (bool|string|array) boolean if is a checkbox or array to match in_array() or a specific value
		) 
	 */
	private function js_vis_code() {	
		if(empty($this->js_vis_cond)) {return false;}
		
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function() {';
		
		foreach($this->js_vis_cond as $field_id => $data) : 
			$data['field_id'] = $field_id;
		?>	
          
			jQuery(document).on(
         		'change lcs-statuschange', 
          		'[name=<?php echo $data['linked_field'] ?>], [name="<?php echo $data['linked_field'] ?>[]"]', 
				<?php echo json_encode($data); ?>,
            	function(e) {
                  	var $linked = jQuery(this);
                    var $field_wrap = jQuery('[name='+ e.data.field_id +'], [name="'+ e.data.field_id +'[]"], [data-field-id="'+ e.data.field_id +'"]').parents('tr');
                    var show = true;
                    
                    switch( typeof(e.data.condition) ) {
                    	case 'boolean' :
                        	if(
                            	(e.data.condition && !$linked.is(':checked')) ||
                                (!e.data.condition && $linked.is(':checked'))
                            ) {
                            	show = false;
                            }
                        	break;
                    
                    	case 'object' :
                        	if(jQuery.inArray( $linked.val(), e.data.condition ) === -1) {
                            	show = false;
                            }
                        	break;
                            
                        default :
                        	if(e.data.condition != $linked.val()) {
                            	show = false;
                            } 
                        	break;
                    } 
                    
                    (show) ? $field_wrap.show() : $field_wrap.hide();         		
       			}
			);
            
            // trigger on page's opening
            jQuery('[name=<?php echo $data['linked_field'] ?>]').trigger('change').trigger('lcs-statuschange');
            	
		<?php
		endforeach;
		
		echo '
			});
		</script>';
	}
		
		
		
	///////////////////////////////////////////////////////////////////////////////////	
	
	
	
	/* 
	 * get validation indexes for stored fields
	 * @return (array)
	 */
	private function get_fields_validation() {
		$indexes = array();
		$a = 0;
		
		foreach($this->fields as $fid => $fval) {
			if($fval['type'] == 'spacer' || $fval['type'] == 'message' || $fval['type'] == 'label_message') {continue;}
			if(isset($fval['no_save'])) {continue;}

			// custom field - manual index addition
			if($fval['type'] == 'custom') {
				if(!isset($fval['validation'])) {continue;}
				
				// allow multi-custom indexes
				if(isset($fval['validation'][0])) {
					foreach($fval['validation'] as $cust_val) {
						$indexes[$a] = $cust_val;
						$a++;	
					}
				}
				else {
					$indexes[$a] = $fval['validation'];
					$a++;			
				}
			}
			
			// dinamically create index
			else {
				$indexes[$a] = array();
				$indexes[$a]['index'] = $fid;
				$indexes[$a]['label'] = $fval['label'];
				
				// required
				if(isset($fval['required']) && $fval['required']) {
					$indexes[$a]['required'] = true;
				}
				
				// min-length
				if(isset($fval['minlen'])) {
					$indexes[$a]['min_len'] = $fval['minlen'];
				}
				
				// maxlenght
				if(isset($fval['maxlen'])) {
					$indexes[$a]['max_len'] = $fval['maxlen'];
				}
				
				// color field
				if($fval['type'] == 'color' && (!isset($fval['allow_transparent']) || !$fval['allow_transparent'])) {
					$indexes[$a]['type'] = 'hex';
				}
				
				// specific types
				if(isset($fval['subtype'])) {
					$indexes[$a]['type'] = $fval['subtype'];
				}
		
				// numeric value range
				if((
					($fval['type'] == 'text' && isset($fval['subtype']) && in_array($fval['subtype'], array('int', 'float'))) ||  
					$fval['type'] == 'val_n_type'
					) && 
					isset($fval['range_from']) && $fval['range_from'] !== '') 
				{	
					$indexes[$a]['min_val'] = (float)$fval['range_from'];
					$indexes[$a]['max_val'] = (float)$fval['range_to'];
				}
				
				// regex validation
				if(isset($fval['regex'])) {
					$indexes[$a]['preg_match'] = $fval['regex'];			
				}	
				
				$a++;
			}
			

			// special cases
			if($fval['type'] == 'val_n_type') {
				$indexes[$a] = array('index'=>$fid.'_type', 'label'=>$fval['label'].' type');	
				$a++;	
			}
		}
		
		return $indexes;
	}
	
	

	/* 
	 * Validate fields - stores errors in $errors and fetched data in $form_data
	 * "simple_form_validator" class must be included 
	 *
	 * @return (bool) true if no errors 
	 */
	public function validate() {
		$indexes = $this->get_fields_validation();
		
		$validator = new simple_fv;
		$validator->formHandle($indexes);
		
		$fdata = $validator->form_val;
		$this->errors = $validator->getErrors('array');	
		if(!$this->errors) {$this->errors = array();}
		
		
		// check nonce
		$noncename = $this->css_prefix .'settings_nonce';
		if(!isset($_POST[$noncename]) || !wp_verify_nonce($_POST[$noncename], 'lcwp')) {
			$this->errors = array( 'Cheating?' );	
		}
		
		
		// clean data and save options
		foreach($fdata as $key => $val) {
			if(!is_array($val)) {
				$fdata[$key] = stripslashes($val);
			} else {
				$fdata[$key] = array();
				foreach($val as $arr_val) {
					$fdata[$key][] = (is_array($arr_val)) ? $arr_val : stripslashes($arr_val);
				}
			}
		}
		
		$this->form_data = $fdata;
		return (empty($this->errors)) ? true : false;
	}
	
	
	
	/* Save form data 
	 * @return (bool)
	 */	
	public function save_data() {
		foreach($this->form_data as $key => $val) {
			
			// skip if is a password placeholder
			if($val == '|||lcwp_sf_psw_placeh|||') {
                continue;
            }
            
			$val = (is_array($val)) ? array_map('sanitize_textarea_field', $val) : sanitize_textarea_field($val);
            
			if($val === false) {delete_option($key);}
			else {update_option($key, $val);}	
		}
	}
}