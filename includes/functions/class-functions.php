<?php
/**
 * @package Easy-Society
 */

interface iFunctions{
	public function generateRandomString($length = 10);
	public function writeToLog($e);
}

class Functions implements iFunctions {
	 public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function writeToLog($e){
	 	error_log('['.date('Y-m-d H:i:s').'] WP Attend Error: '.$e);
	}
 }