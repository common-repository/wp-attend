<?php

use http\Message;

/**
 * Activity Controller.
 * @package WP_Attend
 */

class Activity_Controller extends WP_REST_Controller {

	private $table = 'wp_at_activities';
	private $emailHandler;
	private $functions;

	public function __construct($_emailHandler, $_functions) {
		$this->functions = $_functions;
		$this->emailHandler = $_emailHandler;
	}

	public function register_routes(){
		$version = '1';
		$namespace = 'wp-attend/v' . $version;
		$base = 'activity';
		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
			array(
				'methods'	=> WP_REST_Server::READABLE,
				'callback'	=> array( $this, 'get_activity' ),
				'args'		=> array(),
				'permission_callback'   => array($this, 'check_permission')
			)
		));
		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'	=> WP_REST_Server::CREATABLE,
				'callback'	=> array( $this, 'create_activity'),
				'args'		=> array(),
				'permission_callback'   => array($this, 'check_permission')
			)
		));
		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'	=> WP_REST_Server::EDITABLE,
				'callback'	=> array( $this, 'edit_activity'),
				'args'		=> array(),
				'permission_callback'   => array($this, 'check_permission')
			)
		));
		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
			array(
				'methods'   => WP_REST_Server::DELETABLE,
				'callback'  => array($this, 'delete_activity'),
				'args'      => array(),
				'permission_callback'   => array($this, 'check_permission')
			)
		));
		register_rest_route($namespace, '/' . $base . '/attendance', array(
			array(
				'methods'   =>WP_REST_Server::READABLE,
				'callback'  => array($this, 'set_attendance'),
				'args'      =>array()
			)
		));
		register_rest_route($namespace, '/' . $base . '/attendance' . '/(?P<id>[\d]+)', array(
			array(
				'methods'   =>WP_REST_Server::READABLE,
				'callback'  =>array($this, 'get_attendance'),
				'args'      => array(),
				'permission_callback'   => array($this, 'check_permission')
			)
		));
	}

	public function get_activity($request){
		try {
			global $wpdb;
			if(!is_numeric($request['id'])){
				throw new InvalidArgumentException('id wrong format');
			}
			$id  = (int)$request['id'];
			$res = $wpdb->get_row( "select * from " . $this->table . " where id = " . $id );
			if ( isset( $res ) ) {
				return new WP_REST_Response( $res, 200 );
			}
			throw new Exception('can\'t get');
		}
		catch (InvalidArgumentException $iae){
			$this->functions->writeToLog($iae);
			return new WP_REST_Response(array('message' => $iae->getMessage()), 400);
		}
		catch (Exception $e){
			$this->functions->writeToLog($e);
			return new WP_REST_Response(array('message' => $e->getMessage()), 500);
		}
	}

	public function create_activity($request){
		try {
			global $wpdb;
			$wpdb->query( 'START TRANSACTION' );
			$parameters  = (array) json_decode( $request->get_body() );
			$description = sanitize_text_field($parameters['description']);
			$location    = sanitize_text_field($parameters['location']);
			$time        = sanitize_option('time_format', $parameters['time']);
			$date        = sanitize_option('date_format', $parameters['date']);
			$data        = array(
				'description' => $description,
				'location'    => $location,
				'timestamp'   => $date . ' ' . $time
			);
			$res         = $wpdb->insert( $this->table, $data );
			if ( $res == 1 ) {
				$activity_id = $wpdb->insert_id;
				$data['id']  = $activity_id;
				if ( array_key_exists( 'emailSubscribers', $parameters ) ) {
					if ( $parameters['emailSubscribers'] == true ) {
						$subscribers = $wpdb->get_results( "SELECT * from wp_at_subscriptions where isValid = 1" );
						foreach ( $subscribers as $subscriber ) {
							$code               = $this->functions->generateRandomString( 20 );
							$activitySubscriber = array(
								'activity_id'      => $activity_id,
								'subscription_id'  => $subscriber->id,
								'verificationCode' => $code
							);
							$success            = $wpdb->insert( 'wp_at_activities_attendances', $activitySubscriber );
							if ( $success == 1 ) {
								$emailsuccess = $this->emailHandler->emailSubscribers( $data, $subscriber->email, $code , $subscriber->verificationCode);
								if ( ! $emailsuccess ) {
									$wpdb->query( 'ROLLBACK' );
									throw new Exception('email failed');
								}
							} else {
								$wpdb->query( 'ROLLBACK' );
								throw new Exception('create-attendance-failed');
							}
						}
					}
				}
				$wpdb->query( 'COMMIT' );
				return new WP_REST_Response( $data, 200 );
			}
			throw new Exception('can\'t create');
		}
		catch (InvalidArgumentException $iae){
			$this->functions->writeToLog($iae);
			return new WP_REST_Response(array('message' => $iae->getMessage()), 400);
		}
		catch (Exception $e){
			$this->functions->writeToLog($e);
			return new WP_REST_Response(array('message' => $e->getMessage()), 500);
		}
	}

	public function edit_activity($request){
		try {
			global $wpdb;
			$wpdb->query( 'START TRANSACTION' );
			$parameters = (array) json_decode( $request->get_body() );
			$description = sanitize_text_field($parameters['description']);
			$location    = sanitize_text_field($parameters['location']);
			$time        = sanitize_option('time_format', $parameters['time']);
			$date        = sanitize_option('date_format', $parameters['date']);
			if(!is_numeric($parameters['id'])){
				throw new InvalidArgumentException('id wrong format');
			}
			$id = $parameters['id'];
			$data = array(
				'description' => $description,
				'location'    => $location,
				'timestamp'   => $date . ' ' . $time
			);
			$where = array( 'id' => $id );
			$res = $wpdb->update( $this->table, $data, $where );
			if ( $res == 1 ) {
				if ( array_key_exists( 'emailSubscribers', $parameters ) ) {
					if ( $parameters['emailSubscribers'] == true ) {
						$subscribers = $wpdb->get_results( "SELECT s.*, attend.verificationCode as code from wp_at_subscriptions s " .
						                                   "join wp_at_activities_attendances attend on attend.subscription_id=s.id " .
						                                   "join wp_at_activities a on a.id = attend.activity_id " .
						                                   "where s.isValid = 1 and a.id = " . $id );
						foreach ( $subscribers as $subscriber ) {
							$emailsuccess = $this->emailHandler->emailSubscribersUpdate( $data, $subscriber->email, $subscriber->code , $subscriber->verificationCode);
							if ( ! $emailsuccess ) {
								$wpdb->query( 'ROLLBACK' );
								throw new Exception('email failed');
							}
						}
					}
				}
				$wpdb->query( 'COMMIT' );
				return new WP_REST_Response( $data, 200 );
			}
			throw new Exception('can\'t edit');
		}
		catch (InvalidArgumentException $iae){
			$this->functions->writeToLog($iae);
			return new WP_REST_Response(array('message' => $iae->getMessage()), 400);
		}
		catch (Exception $e){
			$this->functions->writeToLog($e);
			return new WP_REST_Response(array('message' => $e->getMessage()), 500);
		}
	}

	public function delete_activity($request){
		try {
			global $wpdb;
			if(!is_numeric($request['id'])){
				throw new InvalidArgumentException('id wrong format');
			}
			$id  = $request['id'];
			$res = $wpdb->delete( $this->table, array( 'id' => $id ) );
			if ( $res == 1 ) {
				return new WP_REST_Response( $id, 200 );
			}
			throw new Exception('can\'t delete');
		}
		catch (InvalidArgumentException $iae){
			$this->functions->writeToLog($iae);
			return new WP_REST_Response(array('message' => $iae->getMessage()), 400);
		}
		catch (Exception $e){
			$this->functions->writeToLog($e);
			return new WP_REST_Response(array('message' => $e->getMessage()), 500);
		}
	}

	public function set_attendance($request){
		try {
			global $wpdb;
			$code       = sanitize_text_field($request->get_param( 'code' ));
			$willAttend = $request->get_param( 'willattend' );
			if ( ! isset( $code ) || $code == '' ) {
				throw new InvalidArgumentException('invalid code');
			}
			if ( ! isset( $willAttend ) || $willAttend == '' ) {
				throw new InvalidArgumentException('willattend not set');
			}
			$wa = $willAttend == 'true' ? 1 : 0;
			if ( $willAttend == 'null' ) {
				$wa = null;
			}
			$data  = array( 'willAttend' => $wa, 'timestampAnswer' => date( 'Y-m-d H:i:s' ) );
			$where = array( 'verificationCode' => $code );
			$res   = $wpdb->update( 'wp_at_activities_attendances', $data, $where );
			if ( $res == 1 ) {
				$response = $wa == 1 ? 'Will Attend' : 'Will NOT Attend';
				return new WP_REST_Response( 'Attendance successfully set to \'' . $response . '\'', 200 );
			}

			throw new Exception('can\'t set attendance');
		}
		catch (InvalidArgumentException $iae){
			$this->functions->writeToLog($iae);
			return new WP_REST_Response(array('message' => $iae->getMessage()), 400);
		}
		catch (Exception $e){
			$this->functions->writeToLog($e);
			return new WP_REST_Response(array('message' => $e->getMessage()), 500);
		}
	}

	public function get_attendance($request){
		try {
			global $wpdb;
			if(!is_numeric($request['id'])){
				throw new InvalidArgumentException('id wrong format');
			}
			$id  = $request['id'];
			$res = $wpdb->get_results( 'Select a.*, s.id as \'s.id\', s.name as \'s.name\', s.email as \'s.email\' from wp_at_activities_attendances a ' .
			                           'join wp_at_subscriptions s on s.id = a.subscription_id ' .
			                           'where s.isvalid = 1 and a.activity_id = ' . $id .
			                           ' order by s.name asc' );
			if ( isset( $res ) ) {
				return new WP_REST_Response( $res, 200 );
			}
			throw new Exception('can\'t get attendance');
		}
		catch (InvalidArgumentException $iae){
			$this->functions->writeToLog($iae);
			return new WP_REST_Response(array('message' => $iae->getMessage()), 400);
		}
		catch (Exception $e){
			$this->functions->writeToLog($e);
			return new WP_REST_Response(array('message' => $e->getMessage()), 500);
		}
	}

	public function check_permission($request){
		$nonce = $request->get_header('X-WP-Nonce');
		$verify = wp_verify_nonce($nonce, 'wp_rest');
		return $verify;
	}

}
