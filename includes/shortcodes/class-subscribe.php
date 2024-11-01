<?php
/**
 * Subscribe.
 * @package WP_Attend
 */

class Subscribe{
	public function show(){
		$content = '';
		if(isset($_SERVER['QUERY_STRING'])) {
			$queries = array();
			parse_str( $_SERVER['QUERY_STRING'], $queries );

			if ( array_key_exists( 'subresult', $queries ) ) {
				if ( $queries['subresult'] == 'success' ) {
					$content = '<div id="ok-result-msg" style="display: block">'.esc_html__('Subscribed successfully! A verification email has been sent to your email-address.', 'wp-attend').'</div>';
				}
				else if($queries['subresult'] == 'already exists'){
					$content = '<div id="error-result-msg" style="display: block">'.esc_html__('Email address already exists.', 'wp-attend').'</div>';
				}
				else {
					$content = '<div id="error-result-msg" style="display:block;" >'.esc_html__('Something went wrong sending a verification email. Please contact site administrator.', 'wp-attend').'</div>';
				}
			}
		}
		$content .='<div class="form-subscribe" id=subscribeDiv>'.
			           '<form class="form-container" id="subscribeForm">'.
				           '<h3>Subscribe</h3>'.

				           '<label for="name"><b>Name</b></label>'.
				           '<input type="text" placeholder="Name" name="name_sub" required>'.

				           '<label for="email"><b>Email</b></label>'.
				           '<input type="email" placeholder="Email" name="email_sub" required>'.

		                   '<input type="checkbox" name="privacy" required>'.
		                   '<label for="privacy">'.esc_html__('Check here to indicate that you have read and agree to the ', 'wp-attend').' <a href="'.esc_url(get_option('wpattend_privacy_policy_url')).'" target="_blank">'.esc_html__('privacy policy', 'wp-attend').'</a></label>'.

				           '<button type="submit" class="btn" name="btn_subscribe" onclick="createSubscription(event)">Subscribe</button>'.
			           '</form>'.
		           '</div>';
		return $content;
	}
}
