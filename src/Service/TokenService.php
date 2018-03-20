<?php
namespace App\Service;

use \Firebase\JWT\JWT;

class TokenService {
	protected $apiSecret;
	public function __construct($apiSecret){
		$this->apiSecret = $apiSecret;
	}

	public function decodeToken(array $header){
		try {
	    	if(isset($header['HTTP_AUTHORIZATION'][0])){
		    	sscanf( $header['HTTP_AUTHORIZATION'][0], 'Bearer %s',$token);
		        $decoded = JWT::decode($token, $this->apiSecret, array('HS256'));
		    	return [
		        	'status' => 200,
		        	'result' => $decoded
		        ];
		    }
	    } catch (UnexpectedValueException $e) {
			return [
	        	'status' => 500,
	        	'result' => '',
	        	'message' => $e->getMessage()
	        ];
	    }
	}
}