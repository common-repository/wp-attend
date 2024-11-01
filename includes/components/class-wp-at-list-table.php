<?php
/**
 * custom class for displaying at subscriptions
 *
 * @package WP_Attend
 */

class WP_AT_List_Table extends WP_List_Table_C{

	private $functions;
	private $emailHandler;

	public function __construct( $args = array(), $_functions , $_emailHandler) {
		$this->functions = $_functions;
		$this->emailHandler = $_emailHandler;
		parent::__construct( $args );
	}

	function get_columns() {
		$columns = array(
			'name' => 'Name',
			'email' => 'Email',
			'isValid' => 'Valid'
		);
		return $columns;
	}

	function prepare_items() {
		if(!empty($_GET['action'])){
			$id = sanitize_text_field($_GET['id']);
			if(is_numeric($id)){
				if($_GET['action'] == 'delete' && !empty($id)){
					$this->delete_item($id);
				}
				else if($_GET['action'] == 'invalidate' && !empty($id)){
					$this->invalidate_item($id);
				}
				else if($_GET['action'] == 'resend' && !empty($id)){
					$this->resend_validation($id);
				}
			}
		}
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$orderby = (! empty( $_GET['orderby'])) ? sanitize_text_field($_GET['orderby']): 'name';
		$order = (!empty($_GET['order']))? sanitize_text_field($_GET['order']):'asc';
		$search = (!empty($_POST['s']))? sanitize_text_field($_POST['s']):'';
		$current_page = $this->get_pagenum();
		$total_items = $this->get_total_items($search);
		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => 10
		));
		$this->items = $this->fetch_subscriptions($orderby, $order, $current_page, $search);
	}

	function column_default( $item, $column_name ) {
		if($column_name != 'isValid')
			return $item->$column_name;
		else
			return $item->isValid==1?'true':'false';
	}


	function fetch_subscriptions($orderby, $order, $page, $search){
		global $wpdb;
		$where = $search==''?'':"where name like '%".$search."%'";
		return $wpdb->get_results('select * from wp_at_subscriptions '.$where.
		                          ' order by '
		                          . $orderby . ' ' . $order
		                          . ' LIMIT '.(($page - 1) * 10).',10');
	}

	function column_name($item){
		$actions = array(
			'delete' => sprintf('<a href="?page=%s&tab=%s&action=%s&id=%s" onclick="return confirmDelete();">Delete</a>', urlencode($_REQUEST['page']), urlencode($_REQUEST['tab']), 'delete', urlencode($item->id))
		);
		return sprintf('%1$s %2$s', esc_html($item->name), $this->row_actions($actions));
	}

	function column_isValid($item){
		if($item->isValid){
			$actions = array(
				'set invalid' => sprintf('<a href="?page=%s&tab=%s&action=%s&id=%s" onclick="return confirmInvalidate();">Set invalid</a>', urlencode($_REQUEST['page']), urlencode($_REQUEST['tab']), 'invalidate', urlencode($item->id))
			);
			return sprintf('%1$s %2$s', '<div class="valid-image"></div>', $this->row_actions($actions));
		}
		else{
			$actions = array(
				'resend validation' => sprintf('<a href="?page=%s&tab=%s&action=%s&id=%s" onclick="return confirmResend();">Resend validation email</a>', urlencode($_REQUEST['page']), urlencode($_REQUEST['tab']), 'resend', urlencode($item->id))
			);
			return sprintf('%1$s %2$s', '<div class="invalid-image"></div>', $this->row_actions($actions));
		}
	}

	function get_total_items($search){
		global $wpdb;
		$where = $search==''?'':"where name like '%".$search."%'";
		return $wpdb->get_var('select count(*) from wp_at_subscriptions '.$where);
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array('name', false),
			'email' => array('email', false),
			'isValid' => array('isValid', false)
		);
		return $sortable_columns;
	}

	function delete_item($id){
		global $wpdb;
		$wpdb->delete('wp_at_subscriptions', array('id'=>$id));
	}

	function invalidate_item($id){
		global $wpdb;
		$wpdb->update('wp_at_subscriptions', array('isValid' => 0), array('id' => $id));
	}

	function resend_validation($id){
		global $wpdb;
		$code = $this->functions->generateRandomString(20);
		$wpdb->query( 'START TRANSACTION' );
		$res = $wpdb->update('wp_at_subscriptions', array('verificationCode' => $code), array('id' => $id));
		$email = $wpdb->get_var('select email from wp_at_subscriptions where id = '.$id);
		if($res == 1 && $email){
			if($this->emailHandler->emailNewSubscriber($email, $code)){
				$wpdb->query('COMMIT');
				return true;
			}
		}
		$wpdb->query('ROLLBACK');
		return false;
	}


}
