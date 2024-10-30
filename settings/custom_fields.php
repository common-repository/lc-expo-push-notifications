<?php 


/* colors reset button */
function lceps_stats($field_id, $field, $value, $all_vals) {
	global $lceps_tokens_manager;

    ?>
	<table class="widefat lcwp_settings_table lceps_settings_block">
        <tbody>
            <tr class="lceps_stats">
                <td class="lcwp_sf_label">
                    <h1><span id="lceps_tokens_counter"><?php echo number_format_i18n($lceps_tokens_manager->get_tokens_count(), 0); ?></span> <?php esc_html_e('registered device tokens', LCEPS_ML) ?></h1>
                </td>
			</tr>
        </tbody>
    </table>
	<?php
}




/* notification extra parameters */
function lceps_extra_params($field_id, $field, $value, $all_vals) {
	global $lceps_tokens_manager;

    ?>
	<table class="widefat lcwp_settings_table lceps_settings_block extra_notif_params_table">
        <thead>
            <tr>
                <th style="width: 25px;"></th>
                <th><?php esc_html_e('Key') ?> <span class="dashicons dashicons-warning" style="color: #aaa; cursor: help;" title="<?php esc_html_e("changing key name you will lose related notification data", LCEPS_ML) ?>"></span></th>
                <th style="width: 130px;"><?php esc_html_e('Type') ?></th>
                <th><?php esc_html_e('Internal name', LCEPS_ML) ?></th>
                <th><?php esc_html_e('Values (one per line)', LCEPS_ML) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php
        // fix for errors case
        if(isset($_POST['lceps_npd_type'])) {
            $params = array();
            
            $params_key     = (array)$_POST['lceps_npd_key'];
            $params_type    = (array)$_POST['lceps_npd_type'];
            $params_name    = (array)$_POST['lceps_npd_name'];
            $params_val     = (array)$_POST['lceps_npd_val'];

            foreach($params_key as $index => $key) {
                $params[ $key ] = array(
                    'type'  => (isset($params_type[$index])) ? esc_html((string)$params_type[$index]) : '',
                    'name'  => (isset($params_name[$index])) ? esc_html((string)$params_name[$index]) : '',
                    'val'   => (isset($params_val[$index])) ? esc_html((string)$params_val[$index]) : '',
                );    
            }
        }
        else {
            $params = (array)get_option('lceps_notif_params', array());
        }
    
        foreach($params as $key => $data) {
            $textarea_vis = ($data['type'] != 'text') ? '' : 'style="display: none;"'; 
            
            echo '
            <tr>
                <td class="lceps_npd_del"><span class="dashicons dashicons-no-alt" title="'. esc_html__("delete parameter", LCEPS_ML) .'"></span></td>
                <td class="lceps_npd_key">
                    <input type="text" name="lceps_npd_key[]" value="'. esc_attr($key) .'" autocomplete="off" maxlength="100" />
                </td>
                <td>
                    <select name="lceps_npd_type[]" autocomplete="off">
                        <option value="text">'. esc_html__("text") .'</option>
                        <option value="select" '. selected('select', $data['type'], false) .'>'. esc_html__("select", LCEPS_ML) .'</option>
                        <option value="multi-select" '. selected('multi-select', $data['type'], false) .'>'. esc_html__("multi-select", LCEPS_ML) .'</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="lceps_npd_name[]" value="'. esc_attr($data['name']) .'" autocomplete="off" />
                </td>
                <td>
                    <textarea name="lceps_npd_val[]" autocomplete="off" onkeyup="lceps_textAreaAdjust(this)" '. $textarea_vis .'>'. esc_textarea($data['val']) .'</textarea>
                </td>
            </tr>';    
        }
        ?>
        </tbody>
    </table>
	<?php
}