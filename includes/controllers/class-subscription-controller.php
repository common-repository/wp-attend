<?php
/**
 * Subscription controller
 * @package WP_Attend
 */
class Subscription_Controller extends  WP_REST_Controller {
	private $table = 'wp_at_subscriptions';
	private $emailHandler;
	private $functions;

	public function __construct($_emailHandler, $_functions) {
		$this->functions = $_functions;
		$this->emailHandler = $_emailHandler;
	}

	public function register_routes() {
		$version = '1';
		$namespace = 'wp-attend/v' . $version;
		$base = 'subscription';
		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'	=> WP_REST_Server::CREATABLE,
				'callback'	=> array( $this, 'create_subscription' ),
				'args'		=> array(),
				'permission_callback'   => array($this, 'check_permission')
			)
		));
		register_rest_route($namespace, '/' . $base .'/validate', array(
			array(
				'methods'	=> WP_REST_Server::READABLE,
				'callback'	=> array( $this, 'validate' ),
				'args'		=> array(),
			)
		));
		register_rest_route($namespace, '/' . $base . '/unsubscribe', array(
			array(
				'methods'   => WP_REST_Server::READABLE,
				'callback'  => array( $this, 'unsubscribe' ),
				'args'      => array(),
			)
		));
	}

	public function create_subscription($request){
		try {
			global $wpdb;
			$wpdb->query( 'START TRANSACTION' );
			$parameters       = (array) json_decode( $request->get_body() );
			$name             = sanitize_text_field($parameters['name']);
			$email            = sanitize_email($parameters['email']);
			$verificationCode = $this->functions->generateRandomString( 20 );
			$data             = array( 'name'             => $name,
			                           'email'            => $email,
			                           'timestamp'        => date( 'Y-m-d H:i:s' ),
			                           'isValid'          => 0,
			                           'verificationCode' => $verificationCode
			);
			$rowcount         = $wpdb->get_var( "select count(*) from wp_at_subscriptions where email = '$email'" );
			if ( $rowcount > 0 ) {
				throw new InvalidArgumentException('already exists');
			}
			$res = $wpdb->insert( $this->table, $data );
			if ( $res == 1 ) {
				$subscription_id = $wpdb->insert_id;
				$data['id'] = $subscription_id;
				$sent            = $this->emailHandler->emailNewSubscriber( $email, $verificationCode );
				if ( $sent ) {
					$wpdb->query( 'COMMIT' );
					return new WP_REST_Response($data, 200);
				} else {
					$wpdb->query( 'ROLLBACK' );
				}
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

	public function validate($request){
		try {
			global $wpdb;
			$code = sanitize_text_field($request->get_param( 'code' ));
			if ( ! isset( $code ) || $code == '' ) {
				throw new InvalidArgumentException('code-not-set');
			}
			$data   = array( 'isValid' => 1, 'validatedTimestamp' => date( 'Y-m-d H:i:s' ) );
			$where  = array( 'verificationCode' => $code );
			$result = $wpdb->update( $this->table, $data, $where );
			if ( $result == 1 ) {
				return new WP_REST_Response( 'email verified successfully!', 200 );
			}
			throw new Exception('can\'t verify');
		}
		catch (Exception $e){
			$this->functions->writeToLog($e);
			return new WP_REST_Response(array('message' => $e->getMessage()), 500);
		}
	}

	public function unsubscribe($request){
		try{
			global $wpdb;
			$code = sanitize_text_field($request->get_param( 'code' ));
			if(!isset($code) || $code == ''){
				throw  new InvalidArgumentException("code-not-set");
			}
			$res = $wpdb->delete($this->table, array('verificationCode' => $code));
			if($res == 1){
				return new WP_REST_Response( 'Successfully unscubsribed!', 200 );
			}
			throw new Exception('Can\'t unsubscribe');
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
