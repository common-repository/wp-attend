<?php
/**
 * Admin page
 *
 * @package WP_Attend
 */

$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general-settings';
echo '<h1>WP Attend</h1>'.
           '<h2 class="nav-tab-wrapper">'.
                '<a href="?page=wp_attend&tab=general-settings" class="nav-tab '.($active_tab == 'general-settings' ? 'nav-tab-active':'').'">'.__('General', 'wp-attend').'</a>'.
                '<a href="?page=wp_attend&tab=subscriptions" class="nav-tab '.($active_tab == 'subscriptions' ? 'nav-tab-active' : '').'">'.__('Subscriptions', 'wp-attend').'</a>'.
           '</h2>';
if($active_tab == 'general-settings'){
	require WP_ATTEND_PLUGIN_DIR . '/includes/pages/general-settings.php';
}
if($active_tab == 'subscriptions'){
    require WP_ATTEND_PLUGIN_DIR . '/includes/pages/subscriptions.php';
}
