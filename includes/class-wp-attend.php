<?php
/**
* Main class.
*
* @package WP_Attend
*/

class WP_Attend {
	/**
	* Returns instance
	*/
	public static function get_instance(){
		static $instance = null;
		if( is_null($instance)){
			$instance = new WP_Attend();
		}

		return $instance;
	}

	/**
	 * include php files
	 */
	public function includes(){
		require WP_ATTEND_PLUGIN_DIR . 'includes/controllers/class-wp-attend-activity-controller.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/controllers/class-subscription-controller.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/db/class-database.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/shortcodes/class-calendar.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/shortcodes/class-subscribe.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/handlers/class-email-handler.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/functions/class-functions.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/libraries/class-wp-list-table.php';
		require WP_ATTEND_PLUGIN_DIR . 'includes/components/class-wp-at-list-table.php';
	}

	/**
	 * register action hooks and filters
	 */
	public function register(){
	    add_action('plugins_loaded', function(){
	       $loaded = load_plugin_textdomain('wp-attend', false, WP_ATTEND_PLUGIN_DIRNAME . '/languages/');
	    });
		add_action('wp_enqueue_scripts', function(){
			wp_register_script('displayactivityform', WP_ATTEND_PLUGIN_URL . 'js/displayActivityForm.js', array('wp-i18n'));
			wp_set_script_translations('displayactivityform', 'wp-attend');
			wp_localize_script('displayactivityform', 'globalObject', array(
				'homeUrl' => esc_url(home_url()),
				'nonce' =>wp_create_nonce('wp_rest'),
				'loggedin' => is_user_logged_in()
            ));
			wp_enqueue_script('displayactivityform');
			wp_register_script('activityhandler', WP_ATTEND_PLUGIN_URL . 'js/activityhandler.js', array('jquery', 'wp-i18n'));
			wp_localize_script('activityhandler', 'globalObject', array(
				'homeUrl' => esc_url(home_url()),
				'nonce' =>wp_create_nonce('wp_rest'),
                'loggedin' => is_user_logged_in()
			));
			wp_set_script_translations('activityhandler', 'wp-attend');
			wp_enqueue_script('activityhandler');
			wp_register_script('subscriptionhandler', WP_ATTEND_PLUGIN_URL . 'js/subscriptionhandler.js', array('jquery', 'wp-i18n'));
			wp_set_script_translations('subscriptionhandler', 'wp-attend');
			wp_localize_script('subscriptionhandler', 'globalObject', array(
				'homeUrl' => esc_url(home_url()),
				'nonce' =>wp_create_nonce('wp_rest'),
				'loggedin' => is_user_logged_in()
			));
			wp_enqueue_script('subscriptionhandler');
			wp_register_style('calendarstyle', WP_ATTEND_PLUGIN_URL . 'styles/style.css', array());
			wp_enqueue_style( 'calendarstyle');
			wp_register_script('bootstrap', WP_ATTEND_PLUGIN_URL . 'js/bootstrap.js', array('jquery'));
			wp_enqueue_script('bootstrap');
			wp_register_style('bootstrapstyle', WP_ATTEND_PLUGIN_URL . 'styles/bootstrap.css', array());
			wp_enqueue_style('bootstrapstyle');
		});
		add_action('admin_enqueue_scripts', function ($hook){
			if($hook != 'toplevel_page_wp_attend')
				return;
			wp_register_style('adminstyle', WP_ATTEND_PLUGIN_URL . 'styles/admin_style.css', array());
			wp_enqueue_style( 'adminstyle');
			wp_register_script('subscriptionhandler', WP_ATTEND_PLUGIN_URL . 'js/subscriptionhandler.js', array('jquery', 'wp-i18n'));
			wp_localize_script('subscriptionhandler', 'globalObject', array(
				'homeUrl' => esc_url(home_url()),
				'nonce' =>wp_create_nonce('wp_rest'),
				'loggedin' => is_user_logged_in()
			));
			wp_set_script_translations('subscriptionhandler', 'wp-attend');
			wp_enqueue_script('subscriptionhandler');
		});
		add_action('admin_menu', function (){
			add_menu_page('WP Attend', 'WP Attend', 'manage_options', 'wp_attend', function(){
				require WP_ATTEND_PLUGIN_DIR . 'includes/pages/admin_wpattend.php';
			}, 'dashicons-calendar-alt', 98);
		});
		add_action('admin_init', function (){
			add_option('wpattend_privacy_policy_url');
			register_setting('wpattend_general_settings', 'wpattend_privacy_policy_url');
		});
		add_filter('plugin_action_links_'.WP_ATTEND_PLUGIN_NAME, function ($links){
			$settings_link = '<a href="admin.php?page=wp_attend&tab=general-settings">Settings</a>';
			array_unshift( $links, $settings_link);
			return $links;
		});
		add_action('init', function(){
			if( class_exists ('Database')){
				$database = new Database();
				$database->create_tables();
			}
		});
		add_action('rest_api_init', function(){
			if( class_exists ('Activity_Controller') ){
				$activityController = new Activity_Controller(new EmailHandler(), new Functions());
				$activityController->register_routes();
			}
			if(class_exists('Subscription_Controller')){
				$subscriptionController = new Subscription_Controller(new EmailHandler(), new Functions());
				$subscriptionController->register_routes();
			}
		});
		add_shortcode('wp_at_calendar', function(){
			if( class_exists ('Calendar')){
				$calendar = new Calendar();
				return $calendar->show();
			}
		});
		add_shortcode('wp_at_subscribe', function(){
			if(class_exists('Subscribe')){
				$subscribe = new Subscribe();
				return $subscribe->show();
			}
		});
		add_action('admin_notices', function (){
			$privacy_url = get_option('wpattend_privacy_policy_url');
			if(!isset($privacy_url) || $privacy_url == ''){
				?><div class="notice notice-warning is-dismissible">
					<p><?php echo __('Please make sure to set a privacy policy for your WP Attend subscribers in', 'wp-attend')?> <a href="admin.php?page=wp_attend&tab=general-settings"><?php echo __('general settings', 'wp-attend') ?></a>!</p>
				</div>
				<?php
			}
			if(!is_ssl()){
				?><div class="notice notice-warning is-dismissible">
					<p><?php echo __('It\'s recommended to use SSL for enhanced safety when using the WP Attend plugin!', 'wp-attend')?></p>
				</div>
				<?php
			}
		});

	}


}
