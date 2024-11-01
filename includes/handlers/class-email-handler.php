<?php
/**
 * Email handler.
 * @package WP_Attend
 */

interface iEmailHandler{
	public function emailSubscribers($activity, $email, $verification, $subscriptioncode);
	public function emailSubscribersUpdate($activity, $email, $verification, $subscriptioncode);
	public function emailNewSubscriber($email, $verification);
}

class EmailHandler implements iEmailHandler{
	public function emailSubscribersUpdate( $activity, $email, $verification, $subscriptioncode ) {
		$to = $email;
		$subject = get_bloginfo('name').' - Update '.date('Y-m-d', strtotime($activity['timestamp'])).' - '.$activity['description'];
		$headers = 'From: '.get_bloginfo('admin_email') . "\r\n";
		$message = "The following event has been updated: \r\n".
		           "Description: ".$activity['description']."\r\n".
		           "Location: ".$activity['location']."\r\n".
		           "Date and Time: ".date('D d M Y H:i:s', strtotime($activity['timestamp']))."\r\n\r\n".
		           "If you will be attending please click the following link (If you have already responded and do not wish to change your status you may ignore this part):\r\n".
		           home_url('/wp-json/wp-attend/v1/activity/attendance')."?code=".$verification."&willattend=true\r\n\r\n".
		           "If you will NOT be attending please click the following link:\r\n".
		           home_url('/wp-json/wp-attend/v1/activity/attendance')."?code=".$verification."&willattend=false\r\n\r\n".
		           "Thank you\r\n\r\n".
		           "If you wish to unsubscribe and stop receiving these emails please click the following link:\r\n".
				   home_url('/wp-json/wp-attend/v1/subscription/unsubscribe?code='.$subscriptioncode);
		$sent = wp_mail($to, $subject, $message, $headers);
		if(!$sent){
			return false;
		}
		return true;
	}

	public function emailSubscribers($activity, $email, $verification, $subscriptioncode) {
		$to = $email;
		$subject = get_bloginfo('name').' - Attendance '.date('Y-m-d', strtotime($activity['timestamp'])).' - '.$activity['description'];
		$headers = 'From: '.get_bloginfo('admin_email') . "\r\n";
		$message = "You have been invited to the following event: \r\n".
		           "Description: ".$activity['description']."\r\n".
		           "Location: ".$activity['location']."\r\n".
		           "Date and Time: ".date('D d M Y H:i:s', strtotime($activity['timestamp']))."\r\n\r\n".
		           "If you will be attending please click the following link:\r\n".
		           home_url('/wp-json/wp-attend/v1/activity/attendance')."?code=".$verification."&willattend=true\r\n\r\n".
		           "If you will NOT be attending please click the following link:\r\n".
		           home_url('/wp-json/wp-attend/v1/activity/attendance')."?code=".$verification."&willattend=false\r\n\r\n".
		           "Thank you\r\n\r\n".
		           "If you wish to unsubscribe and stop receiving these emails please click the following link:\r\n".
		           home_url('/wp-json/wp-attend/v1/subscription/unsubscribe?code='.$subscriptioncode);
		$sent = wp_mail($to, $subject, $message, $headers);
		if(!$sent){
			return false;
		}
		return true;
	}

	public function emailNewSubscriber( $email, $verification ) {
		$to = $email;
		$subject = get_bloginfo('name').' verification';
		$headers = 'From: '.get_bloginfo('admin_email') . "\r\n";
		$message = 'Please click the following link to verify your email: '.home_url('/wp-json/wp-attend/v1/subscription/validate').'?code='.$verification;
		$sent = wp_mail($to, $subject, $message, $headers);
		if($sent)
			return true;
		return false;
	}
}