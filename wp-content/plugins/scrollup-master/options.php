<div id="wpbody">
	<div class="wrap">
		<h2><?php _e('Scroll to Top Settings') ?></h2>
		<form action="options.php" method="post">
			<?php
				$options = self::get_options();
				settings_fields( 'sis_scrooltotop_settings' );
			?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Button Position from Bottom') ?></label>
						</th>
						<td>
							<input type="number" name="sis_scrooltotop_settings[btn_bottom]" value="<?php esc_attr_e($options['btn_bottom']); ?>">
							<p class="description"><?php _e('Set button position from bottom. Defaults position is &lsquo; 20 &lsquo;') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Button Position from Right') ?></label>
						</th>
						<td>
							<input type="number" name="sis_scrooltotop_settings[btn_right]" id="" value="<?php esc_attr_e($options['btn_right']); ?>">
							<p class="description"><?php _e('Set button position from right. Defaults position is &lsquo; 20 &lsquo;') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Scroll Distance') ?></label>
						</th>
						<td>
							<input type="number" name="sis_scrooltotop_settings[scrolldistance]" id="scrolldistance" value="<?php esc_attr_e($options['scrolldistance']); ?>">

							<p class="description"><?php _e('Distance from top in pixels before showing element. Default distance is &lsquo; 300 &lsquo; pixels ') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Scrollup Tooltip Title') ?></label>
						</th>
						<td>
							<input type="text" name="sis_scrooltotop_settings[tooltiptitle]" id="" value="<?php esc_attr_e($options['tooltiptitle']); ?>">
							<p class="description"><?php _e('Set a custom title if required. This title will show when you put your mouse pointer on scrollup button. Defaults title is &lsquo; Scroll to top &lsquo;') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Icon Type') ?></label>
						</th>
						<td>
							<select name="sis_scrooltotop_settings[icon_type]" id="icon_type">
								<option value="arrow-up" <?php selected( $options['icon_type'], 'arrow-up' ); ?>>arrow-up</option>
								<option value="angle-up" <?php selected( $options['icon_type'], 'angle-up' ); ?>>angle-up</option>
								<option value="angle-double-up" <?php selected( $options['icon_type'], 'angle-double-up' ); ?>>angle-double-up</option>
								<option value="chevron-up" <?php selected( $options['icon_type'], 'chevron-up' ); ?>>chevron-up</option>
								<option value="long-arrow-up" <?php selected( $options['icon_type'], 'long-arrow-up' ); ?>>long-arrow-up</option>
							</select>

							<p class="description"><?php _e('Choose Icon Type') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Button Type') ?></label>
						</th>
						<td>
							<select name="sis_scrooltotop_settings[button_type]" id="button_type">
								<option value="square" <?php selected( $options['button_type'], 'square' ); ?>>Square</option>
								<option value="circle" <?php selected( $options['button_type'], 'circle' ); ?>>Circle</option>
							</select>

							<p class="description"><?php _e('Choose Button Type') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Icon Color') ?></label>
						</th>
						<td>
							<input type="text" class="colorpicker" id="color" name="sis_scrooltotop_settings[color]" id="" value="<?php esc_attr_e($options['color']); ?>" data-default-color="#ffffff">

							<p class="description"><?php _e('Choose icon color.') ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Icon Color on Hover') ?></label>
						</th>
						<td>
							<input type="text" class="colorpicker" id="hover_color" name="sis_scrooltotop_settings[hover_color]" id="" value="<?php esc_attr_e($options['hover_color']); ?>" data-default-color="#ffffff">
							<p class="description"><?php _e('Choose icon color on hover.') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Icon Background Color') ?></label>
						</th>
						<td>
							<input type="text" class="colorpicker" id="bg_color" name="sis_scrooltotop_settings[bg_color]" id="" value="<?php esc_attr_e($options['bg_color']); ?>" data-default-color="#494949">
							<p class="description"><?php _e('Choose icon background color.') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for=""><?php _e('Icon Background Color on Hover') ?></label>
						</th>
						<td>
							<input type="text" class="colorpicker" id="bg_hover_color" name="sis_scrooltotop_settings[bg_hover_color]" id="" value="<?php esc_attr_e($options['bg_hover_color']); ?>" data-default-color="#494949">
							<p class="description"><?php _e('Choose icon background color on hover.') ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" value="<?php _e('Save Changes') ?>" class="button button-primary" id="submit" name="submit"></p>
		</form>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
	    $('.colorpicker').wpColorPicker();
	});
</script>