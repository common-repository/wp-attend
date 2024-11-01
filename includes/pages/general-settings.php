<h3><?php echo __('General settings', 'wp-attend')?></h3>

<form method="post" action="options.php">
	<?php settings_fields('wpattend_general_settings');?>
	<?php do_settings_sections('wp_attend');?>
	<label for="wpattend_privacy_policy_url"><?php echo __('Privacy policy URL', 'wp-attend')?></label>
	<input type="text" id="wpattend_privacy_policy_url" name="wpattend_privacy_policy_url"
	       value="<?php echo get_option('wpattend_privacy_policy_url');?>" />
	<?php submit_button();?>
</form>