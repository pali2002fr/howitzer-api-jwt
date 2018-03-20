<?php

use Slim\Http\Request;
use Slim\Http\Response;

use \Firebase\JWT\JWT;

class authenticateMiddleware
{	
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
    	$username = trim($request->getParam('username'));
    	$password = trim($request->getParam('password'));

    	if (!$username || !$password) {
	    	$response = $response->withJson("Parameter missing.", 400);
		}

		$conditions = array(
        	'username' => array(
        		'value' => $username,
        		'operator' => '='
        	)
        );
		$user = $this->container->UserMapper->findAll($conditions);

		if (!$user) {
			$response = $response->withJson("User's username or/and password is incorrect.", 400);
		} else if(password_verify($password, $user[0]->getPassword())) {
			try {
				$token_from_db = false;
			    $conditions = array(
		        	'expiration_date' => array(
		        		'value' => time(),
		        		'operator' => '>'
		        	),
		        	'user_id' => array(
		        		'value' => $user[0]->getId(),
		        		'operator' => '='
		        	)
		        );

		        $token_from_db = $this->container->TokenMapper->findAll($conditions);
		        
			    if ($token_from_db) {
					$response = $response->withJson($token_from_db[0]->getToken(), 200);
	                $response = $next($request, $response);
            	}
			} catch (PDOException $e) {
			    $response = $response->withJson($e->getMessage(), 500);
		    }

		    // Create a new token if a user is found but not a token corresponding to whom.
	        if ($user && empty($token_from_db)) {
	            $key = "monmotsecret";
	            $payload = array(
	                "iss"     => "http://35.190.159.55:8080",
	                "iat"     => time(),
	                "exp"     => time() + (3600 * 24 * 15),
	                "context" => [
	                    "user" => [
	                        "username" => $user[0]->getUsername(),
	                        "user_id"    => $user[0]->getId()
	                    ]
	                ]
	            );

	            try {
	                $jwt = JWT::encode($payload, $key);
	            } catch (Exception $e) {
					$response = $response->withJson($e->getMessage(), 500);
	            }
	            
	            try {
	                $_token = new App\Model\Token($jwt, $user[0], date("Y-m-d H:i:s"), $payload['exp']);
	            	$this->container->TokenMapper->insert($_token);
	            	$response = $response->withJson($jwt, 200);
					$response = $next($request, $response);
	            } catch (PDOException $e) {
					$response = $response->withJson($e->getMessage(), 500);
	            }
	        }
		} else {
			$response = $response->withJson("User's username or/and password is incorrect.", 400);
		}

        return $response;
    }
}

class restrictionMiddleware
{
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

	public function __invoke(Request $request, Response $response, $next)
    {
    	$decoded = $this->container->TokenService->decodeToken($request->getHeaders());

	    if (isset($decoded) && $decoded['status'] === 200) {
	    	$conditions = array(
	    		'expiration_date' => array(
	        		'value' => time(),
	        		'operator' => '>'
	        	),
	        	'user_id' => array(
	        		'value' => $decoded['result']->context->user->user_id,
	        		'operator' => '='
	        	)
	        );

	    	try {
		    	$token_from_db = $this->container->TokenMapper->findAll($conditions);
	            if ($token_from_db) {
	            	$response = $next($request, $response);
	            }
	        } catch (PDOException $e) {
				$response = $response->withJson($e->getMessage(), 500);
	        }
	    } else {
			$response = $response->withJson("Unauthorized", 401);
	    }

	    return $response;
    }
}




