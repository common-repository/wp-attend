<?php
/**
 * @package WP_Attend
 */

 class Database {
	 public function register(){
		 add_action('init', array($this, 'create_tables'));
	 }
	 
	 public function create_tables(){
		 global $wpdb;
		 require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		 
		 $sql = "CREATE TABLE wp_at_activities(
		 id bigint(20) NOT NULL AUTO_INCREMENT,
		 description varchar(255) NOT NULL,
		 location text NOT NULL,
		 timestamp datetime NOT NULL,
		 withAttendance bool NOT NULL,
		 UNIQUE KEY id (id))ENGINE = InnoDB;";	 	  
		 maybe_create_table("wp_at_activities", $sql);
		 
		 $sql = "CREATE TABLE wp_at_subscriptions(
		 id bigint(20) NOT NULL AUTO_INCREMENT,
		 name varchar(255) NOT NULL,
		 email varchar(255) NOT NULL,
		 timestamp datetime NOT NULL,
		 validatedTimestamp datetime NOT NULL,
		 isValid bool,
		 verificationCode varchar(255) NOT NULL,
		 UNIQUE KEY id (id))ENGINE = InnoDB;";
		 maybe_create_table("wp_at_subscriptions", $sql);
		 
		 $sql = "CREATE TABLE wp_at_activities_attendances(
		 activity_id bigint(20) NOT NULL,
		 subscription_id bigint(20) NOT NULL,
		 willAttend bool,
		 verificationCode varchar(255) NOT NULL,
		 timestampAnswer datetime,
		 UNIQUE KEY (activity_id, subscription_id),
		 CONSTRAINT activity_fk FOREIGN KEY (activity_id) REFERENCES wp_at_activities(id) ON DELETE CASCADE,
		 CONSTRAINT subscription_fk FOREIGN KEY (subscription_id) REFERENCES wp_at_subscriptions(id) ON DELETE CASCADE)ENGINE = InnoDB;";
		 maybe_create_table("wp_at_activities_attendances", $sql);
	 }
 }