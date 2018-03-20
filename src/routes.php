<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->get('/home', function (Request $request, Response $response){
    return $response->withJson('Home',200);
}); 

/*
 * Authenticate
 */
$app->post('/login', function (Request $request, Response $response, $args) {
})->add( new authenticateMiddleware($app->getContainer()) );

/*
 * Register new user
 */
$app->post('/register', function (Request $request, Response $response, $args){
    try {
    	$firstname = $request->getParam('firstname');
    	$lastname = $request->getParam('lastname');
    	$username = $request->getParam('username');
    	$password = $request->getParam('password');

    	if (strlen($password) < 5 || strlen($password) > 8) {
        	return $response->withJson("The user password is invalid.", 400);
        }

        $conditions = array(
        	'username' => array(
        		'value' => $username,
        		'operator' => '='
        	)
        );
        $user_exists = $this->UserMapper->findAll($conditions);
        if(!$user_exists){
        	$password_hashed = password_hash(trim($password), PASSWORD_DEFAULT);
            $user = new App\Model\User($firstname, $lastname, $username, $password_hashed);
            $user_id = $this->UserMapper->insert( $user );
            return $response->withJson($user_id, 200);
        }
        return $response->withJson("User already exists!", 400);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(),500);
    }
});

/*
 * Get User already exists
 */
$app->get('/users/{username:[a-zA-Z0-9]+}/alreadyexists', function (Request $request, Response $response, $args){
    try {
        $result = null;

        $users_obj = $this->UserMapper->findByUsername($args['username']);
        if(!empty($users_obj)) {
            $result = true;
        }
        return $response->withJson($result, 200);
    } catch(PDOException $e) {
        return $response->withJson($e->getMessage(), 500);
    }
});

/*
 * Get users
 */
$app->get('/users', function (Request $request, Response $response){
    try {
        $users = array();
        $users_obj = $this->UserMapper->findAll();
        if($users_obj){
            foreach ($users_obj as $key => $value) {
                $users[$key]['id'] = $value->getId();
                $users[$key]['username'] = $value->getUsername();
                $users[$key]['firstname'] = $value->getFirstname();
                $users[$key]['lastname'] = $value->getLastname();
            } 
        }
        return $response->withJson($users, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) );

/*
 * Get single user
 */
$app->group('/users/{userid:[0-9]+}', function (){
    $this->get('', function(Request $request, Response $response, $args){
        try {
            $user = array();
            $user_obj = $this->UserMapper->findById($args['userid']);
            if($user_obj){
                $user['id'] = $user_obj->getId();
                $user['username'] = $user_obj->getUsername();
                $user['firstname'] = $user_obj->getFirstname();
                $user['lastname'] = $user_obj->getLastname();
                return $response->withJson($user, 200);
            }
            return $response->withJson("User does not exist!", 404);
        } catch(PDOException $e) {
            return $response->withJson($e->getMessage(), 500);
        }    
    });

    /*
     * Get results by single user
     */
    $this->get('/results[/{orderby:[a-zA-Z]+}]', function (Request $request, Response $response, $args){
        try {
            $userid = $args['userid'];
            $user_obj = $this->UserMapper->findById($userid);
            if($user_obj){
                $_toJson = array();
                $orderby = isset($args['orderby']) ? ( in_array($args['orderby'], ['asc','desc']) ? $args['orderby'] : 'desc') : "desc";
                
                $orderby = "id " . $orderby;
                $conditions = [
                    "id_user" => [
                        "value" => $userid,
                        "operator" => "="
                    ]
                ];

                $results_obj = $this->ResultMapper->findAll($conditions, $orderby);
                if($results_obj){
                    foreach ($results_obj as $key => $value) {
                        $_toJson[$key]['id'] = $value->getId();
                        $_toJson[$key]['user']['id'] = $value->getUser()->getId();
                        $_toJson[$key]['user']['username'] = $value->getUser()->getUsername();
                        $_toJson[$key]['user']['firstname'] = $value->getUser()->getFirstname();
                        $_toJson[$key]['user']['lastname'] = $value->getUser()->getLastname();
                        $_toJson[$key]['shot']['howitzer']['id'] = $value->getShot()->getHowitzer()->getId();
                        $_toJson[$key]['shot']['howitzer']['name'] = $value->getShot()->getHowitzer()->getWeight();
                        $_toJson[$key]['shot']['target']['id'] = $value->getShot()->getTarget()->getId();
                        $_toJson[$key]['shot']['target']['size'] = $value->getShot()->getTarget()->getSize();
                        $_toJson[$key]['shot']['distance']['id'] = $value->getShot()->getDistance()->getId();
                        $_toJson[$key]['shot']['distance']['distance'] = $value->getShot()->getDistance()->getDistance();
                        $_toJson[$key]['shot']['speed']['id'] = $value->getShot()->getSpeed()->getId();
                        $_toJson[$key]['shot']['speed']['speed'] = $value->getShot()->getSpeed()->getSpeed();
                        $_toJson[$key]['shot']['angle']['id'] = $value->getShot()->getAngle()->getId();
                        $_toJson[$key]['shot']['angle']['angle'] = $value->getShot()->getAngle()->getAngle();
                        $_toJson[$key]['shot']['created_date'] = $value->getShot()->getCreated_date();
                        $_toJson[$key]['hit'] = $value->getHit();
                        $_toJson[$key]['impact'] =  $value->getImpact();
                    };
                }
                return $response->withJson($_toJson, 200);
            }
            return $response->withJson("User does not exist!", 404);
        } catch(PDOException $e) {
            return $response->withJson($e->getMessage(), 500);
        }
    }); 
})->add( new restrictionMiddleware($app->getContainer()) );  


/*
 * Update single user: firstname, lastname
 */
$app->put('/users/{userid:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $user = array();
        $userid = $args['userid'];
        $newfirstname = trim($request->getParam('firstname'));
        $newlastname = trim($request->getParam('lastname'));
        $user_obj = $this->UserMapper->findById($userid);

        if($user_obj){
            $user_obj->setFirstname($newfirstname);
            $user_obj->setLastname($newlastname);
            $this->UserMapper->update($user_obj);
            return $response->withJson(true, 200);
        }
        return $response->withJson("User does not exist!", 404);
    } catch(PDOException $e) {
        return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Update single user: username
 */
$app->put('/users/{userid:[0-9]+}/update/username', function (Request $request, Response $response, $args){
    try {
        $user = array();
        $userid = $args['userid'];
        $newusername = trim($request->getParam('username'));

        if($this->UserMapper->findByUsername($newusername)){
            return $response->withJson("Username already exists!", 400);
        }
        $user_obj = $this->UserMapper->findById($userid);
        if($user_obj){
            $user_obj->setUsername($newusername);
            $conditions = array(
                'expiration_date' => array(
                    'value' => microtime(true),
                    'operator' => '>'
                ),
                'user_id' => array(
                    'value' => $user_obj->getId(),
                    'operator' => '='
                )
            );
            $token_obj_arr = $this->TokenMapper->findAll($conditions);
            if($token_obj_arr){
                $this->TokenMapper->update(
                    $token_obj_arr[0]->setExpiration_date(microtime(true))
                );
            }
            if($this->UserMapper->update($user_obj) > 0){
                return $response->withJson(true, 200);
            }
            return $response->withJson("Something went wrong", 500);
        }
        return $response->withJson("User does not exist!", 404);
    } catch(PDOException $e) {
        return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Change password
 */
$app->put('/users/{userid:[0-9]+}/update/password', function (Request $request, Response $response, $args){
    try {
        $userid = $args['userid'];
        $oldpassword = trim($request->getParam('oldpassword'));
        $newpassword = trim($request->getParam('newpassword'));

        if (strlen($oldpassword) < 5 || strlen($oldpassword) > 8) {
            return $response->withJson("The user old password is invalid.", 400);
        }

        if (strlen($newpassword) < 5 || strlen($newpassword) > 8) {
            return $response->withJson("The user new password is invalid.", 400);
        }

        $user_exists = $user_obj = $this->UserMapper->findById($userid);
        if($user_exists){
            if(password_verify($oldpassword, $user_exists->getPassword())) {
                $password_hashed = password_hash(trim($newpassword), PASSWORD_DEFAULT);
                $user_exists->setPassword($password_hashed);
                $conditions = array(
                    'expiration_date' => array(
                        'value' => microtime(true),
                        'operator' => '>'
                    ),
                    'user_id' => array(
                        'value' => $user_exists->getId(),
                        'operator' => '='
                    )
                );
                $token_obj_arr = $this->TokenMapper->findAll($conditions);
                if($token_obj_arr){
                    $this->TokenMapper->update(
                        $token_obj_arr[0]->setExpiration_date(microtime(true))
                    );
                }
                if($this->UserMapper->update($user_exists) > 0){
                    return $response->withJson(true);
                }
                return $response->withJson("Something went wrong", 500);
            }
            return $response->withJson("Old password is incorrect!", 400);
        }
        return $response->withJson("User does not exist!", 404);
    } catch(PDOException $e) {
        return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) );

/*
 * Delete single user
 */
$app->delete('/users/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $user = array();
        $userid = $args['id'];
        $user_obj = $this->UserMapper->findById($userid);

        if($user_obj){
            //Result
            $this->ResultMapper->deleteByUser($user_obj);
            //Shot
            $this->ShotMapper->deleteByUser($user_obj);
            //Token
            $this->TokenMapper->deleteByUser($user_obj);
            //User
            if($this->UserMapper->delete($user_obj) == 1){
                return $response->withJson(true, 200);
            }
            return $response->withJson("Something went wrong", 500);
        }
        return $response->withJson("User does not exist!", 404);
    } catch(PDOException $e) {
        return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get howitzers
 */
$app->get('/howitzers', function (Request $request, Response $response){
    try {
        $howitzers = array();
        $howitzers_obj = $this->HowitzerMapper->findAll();
        if($howitzers_obj){
            foreach ($howitzers_obj as $key => $value) {
                $howitzers[$key]['id'] = $value->getId();
                $howitzers[$key]['weight'] = $value->getWeight();
            }
        }
        return $response->withJson($howitzers, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get single howitzer
 */
$app->get('/howitzers/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $howitzer = array();
        $howitzers_obj = $this->HowitzerMapper->findById($args['id']);
        if($howitzers_obj){
            $howitzer['id'] = $howitzers_obj->getId();
            $howitzer['weight'] = $howitzers_obj->getWeight();
            return $response->withJson($howitzer, 200);
        }
        return $response->withJson("Howitzer does not exist!", 404);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Create new howitzer
 */
$app->post('/howitzers', function (Request $request, Response $response){
    try {
    	$weight = $request->getParam('weight');
    	$conditions = array(
        	'weight' => array(
        		'value' => $weight,
        		'operator' => '='
        	)
        );
        $howitzer_exists = $this->HowitzerMapper->findAll($conditions);
        if(!$howitzer_exists){
            $howitzer = new Howitzer($weight);
            $howitzer_id = $this->HowitzerMapper->insert( $howitzer );
            return $response->withJson($howitzer_id, 200);
        }
        return $response->withJson("Howitzer already exists!", 400);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get distances
 */
$app->get('/distances', function (Request $request, Response $response){
    try {
        $distances = array();
        $distances_obj = $this->DistanceMapper->findAll();
        if($distances_obj){
            foreach ($distances_obj as $key => $value) {
                $distances[$key]['id'] = $value->getId();
                $distances[$key]['distance'] = $value->getDistance();
            }
        } 
        return $response->withJson($distances, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get single distance
 */
$app->get('/distances/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $distance = array();
        $distance_obj = $this->DistanceMapper->findById($args['id']);
        if($distance_obj){
            $distance['id'] = $distance_obj->getId();
            $distance['distance'] = $distance_obj->getDistance();
            return $response->withJson($distance);
        }
        return $response->withJson("Distance does not exist!", 404);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Create new distance
 */
$app->post('/distances', function (Request $request, Response $response, $args){
    try {
    	$distance = $request->getParam('distance');
    	$conditions = array(
        	'distance' => array(
        		'value' => $distance,
        		'operator' => '='
        	)
        );
        $distance_exists = $this->DistanceMapper->findAll($conditions);
        if(!$distance_exists){
            $distance = new Distance( $distance );
            $distance_id = $distanceMapper->insert( $distance );
            return $response->getBody()->write($distance_id);
        }
        return $response->withJson("Distance already exists!", 400);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get targets
 */
$app->get('/targets', function (Request $request, Response $response){
    try {
        $targets = array();
        $targets_obj = $this->TargetMapper->findAll();
        if($targets_obj){
            foreach ($targets_obj as $key => $value) {
                $targets[$key]['id'] = $value->getId();
                $targets[$key]['size'] = $value->getSize();
            }
        } 
        return $response->withJson($targets, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get targets
 */
$app->get('/targets/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $target = array();
        $target_obj = $this->TargetMapper->findById($args['id']);
        if($target_obj){
            $target['id'] = $target_obj->getId();
            $target['size'] = $target_obj->getSize();
            return $response->withJson($target);
        }
        return $response->withJson("Target does not exist!", 404);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Create new target
 */
$app->post('/targets', function (Request $request, Response $response){
    try {
    	$size = $request->getParam('size');
    	$conditions = array(
        	'target' => array(
        		'value' => $target,
        		'operator' => '='
        	)
        );
        $target_exists = $this->TargetMapper->findAll($conditions);
        if(!$target_exists){
            $target = new Target( $size );
            $target_id = $targetMapper->insert( $target );
            return $response->withJson($target_id, 200);
        }
        return $response->withJson("Target already exists!", 400);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get speeds
 */
$app->get('/speeds', function (Request $request, Response $response){
    try {
        $speeds = array();
        $speeds_obj = $this->SpeedMapper->findAll();
        if($speeds_obj){
            foreach ($speeds_obj as $key => $value) {
                $speeds[$key]['id'] = $value->getId();
                $speeds[$key]['speed'] = $value->getSpeed();
            }
        }
        return $response->withJson($speeds, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get single speed
 */
$app->get('/speeds/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $speed = array();
        $speed_obj = $this->SpeedMapper->findById($args['id']);
        if($speed_obj){
            $speed['id'] = $speed_obj->getId();
            $speed['speed'] = $speed_obj->getSpeed();
            return $response->withJson($speed, 200);
        }
        return $response->withJson("Speed does not exist!", 404);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Create new speed
 */
$app->post('/speeds', function (Request $request, Response $response){
    try {
    	$speed = $request->getParam('speed');
    	$conditions = array(
        	'speed' => array(
        		'value' => $speed,
        		'operator' => '='
        	)
        );
        $speed_exists = $this->SpeedMapper->findAll($conditions);
        if(!$speed_exists){
            $speed = new Speed($speed);
            $speed_id = $speedMapper->insert( $speed );
            return $response->getBody()->write($speed_id);
        }
        return $response->withJson("Speed already exists!", 400);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get angles
 */
$app->get('/angles', function (Request $request, Response $response){
    try {
        $angles = array();
        $angles_obj = $this->AngleMapper->findAll();
        if($angles_obj){
            foreach ($angles_obj as $key => $value) {
                $angles[$key]['id'] = $value->getId();
                $angles[$key]['angle'] = $value->getAngle();
            } 
        }
        return $response->withJson($angles, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get single angle
 */
$app->get('/angles/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $angle = array();
        $angle_obj = $this->AngleMapper->findById($args['id']);
        if($angle_obj){
            $angle['id'] = $angle_obj->getId();
            $angle['angle'] = $angle_obj->getAngle();
            return $response->withJson($angle);
        }
        return $response->withJson("Angle does not exist!", 404);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Create new angle
 */
$app->post('/angles', function (Request $request, Response $response, $args){
    try {
    	$angle = $request->getParam('angle');
    	$conditions = array(
        	'angle' => array(
        		'value' => $angle,
        		'operator' => '='
        	)
        );
        $angle_exists = $this->AngleMapper->findAll($conditions);
        if(!$angle_exists){
            $angle = new Angle($angle);
            $angle_id = $angleMapper->insert( $angle );
            return $response->withJson($angle_id, 200);
        }
    	return $response->withJson("Angle already exists!", 400);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get shots
 */
$app->get('/shots', function (Request $request, Response $response){
    try {
        $_toJson = array();
        $shots_obj = $this->ShotMapper->findAll();
        if($shots_obj){
            foreach ($shots_obj as $key => $value) {
                $_toJson[$key]['id'] = $value->getId();
                $_toJson[$key]['user']['id'] = $value->getUser()->getId();
                $_toJson[$key]['user']['name'] = $value->getUser()->getName();
                $_toJson[$key]['howitzer']['id'] = $value->getHowitzer()->getId();
                $_toJson[$key]['howitzer']['weight'] = $value->getHowitzer()->getWeight();
                $_toJson[$key]['target']['id'] = $value->getTarget()->getId();
                $_toJson[$key]['target']['size'] = $value->getTarget()->getSize();
                $_toJson[$key]['distance']['id'] = $value->getDistance()->getId();
                $_toJson[$key]['distance']['distance'] = $value->getDistance()->getDistance();
                $_toJson[$key]['speed']['id'] = $value->getSpeed()->getId();
                $_toJson[$key]['speed']['speed'] = $value->getSpeed()->getSpeed();
                $_toJson[$key]['angle']['id'] = $value->getAngle()->getId();
                $_toJson[$key]['angle']['angle'] = $value->getAngle()->getAngle();
            }  
        }
        return $response->withJson($_toJson, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get single shot
 */
$app->get('/shots/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $_toJson = array();
        $shot_obj = $this->ShotMapper->findById($args['id']);
        if($shot_obj){
            $_toJson['id'] = $shot_obj->getId();
            $_toJson['user']['id'] = $shot_obj->getUser()->getId();
            $_toJson['user']['name'] = $shot_obj->getUser()->getName();
            $_toJson['howitzer']['id'] = $shot_obj->getHowitzer()->getId();
            $_toJson['howitzer']['weight'] = $shot_obj->getHowitzer()->getWeight();
            $_toJson['target']['id'] = $shot_obj->getTarget()->getId();
            $_toJson['target']['size'] = $shot_obj->getTarget()->getSize();
            $_toJson['distance']['id'] = $shot_obj->getDistance()->getId();
            $_toJson['distance']['distance'] = $shot_obj->getDistance()->getDistance();
            $_toJson['speed']['id'] = $shot_obj->getSpeed()->getId();
            $_toJson['speed']['speed'] = $shot_obj->getSpeed()->getSpeed();
            $_toJson['angle']['id'] = $shot_obj->getAngle()->getId();
            $_toJson['angle']['angle'] = $shot_obj->getAngle()->getAngle();
            return $response->withJson($_toJson, 200);
        }
        return $response->withJson("Shot does not exist!", 404);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Create new shot
 */
$app->post('/shots', function (Request $request, Response $response){
    try {
    	$user_id = $request->getParam('user_id');
    	$howitzer_id = $request->getParam('howitzer_id');
    	$target_id = $request->getParam('target_id');
    	$distance_id = $request->getParam('distance_id');
    	$speed_id = $request->getParam('speed_id');
    	$angle_id = $request->getParam('angle_id');

        $user = $this->UserMapper->findById($user_id);
        $howitzer = $this->HowitzerMapper->findById($howitzer_id);
        $target = $this->TargetMapper->findById($target_id);
        $distance = $this->DistanceMapper->findById($distance_id);
        $speed = $this->SpeedMapper->findById($speed_id);
        $angle = $this->AngleMapper->findById($angle_id);

        if(!$user) {
        	return $response->withJson("User does not exist!", 400);
        }
        if(!$howitzer) {
        	return $response->withJson("Howitzer does not exist!", 400);
        }
        if(!$target) {
        	return $response->withJson("Target does not exist!", 400);
        }
        if(!$distance) {
        	return $response->withJson("Distance does not exist!", 400);
        }
        if(!$speed) {
        	return $response->withJson("Speed does not exist!", 400);
        }
        if(!$angle) {
        	return $response->withJson("Angle does not exist!", 400);
        }

        $shot_id = $this->ShotMapper->insert( 
            $user, 
            $howitzer, 
            $target, 
            $distance, 
            $speed, 
            $angle
        );
        return $response->withJson($shot_id, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get results
 */
$app->get('/results', function (Request $request, Response $response){
    try {
        $_toJson = array();
        $results_obj = $this->ResultMapper->findAll($conditions, $filters);
        if($results_obj){
            foreach ($results_obj as $key => $value) {
                $_toJson[$key]['id'] = $value->getId();
                $_toJson[$key]['user']['id'] = $value->getUser()->getId();
                $_toJson[$key]['user']['name'] = $value->getUser()->getName();
                $_toJson[$key]['shot']['howitzer']['id'] = $value->getShot()->getHowitzer()->getId();
                $_toJson[$key]['shot']['howitzer']['name'] = $value->getShot()->getHowitzer()->getWeight();
                $_toJson[$key]['shot']['target']['id'] = $value->getShot()->getTarget()->getId();
                $_toJson[$key]['shot']['target']['size'] = $value->getShot()->getTarget()->getSize();
                $_toJson[$key]['shot']['distance']['id'] = $value->getShot()->getDistance()->getId();
                $_toJson[$key]['shot']['distance']['distance'] = $value->getShot()->getDistance()->getDistance();
                $_toJson[$key]['shot']['speed']['id'] = $value->getShot()->getSpeed()->getId();
                $_toJson[$key]['shot']['speed']['speed'] = $value->getShot()->getSpeed()->getSpeed();
                $_toJson[$key]['shot']['angle']['id'] = $value->getShot()->getAngle()->getId();
                $_toJson[$key]['shot']['angle']['angle'] = $value->getShot()->getAngle()->getAngle();
                $_toJson[$key]['hit'] = $value->getHit();
                $_toJson[$key]['impact'] =  $value->getImpact();
            };
        }
        return $response->withJson($_toJson, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get result
 */
$app->get('/results/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $_toJson = array();
        $result_obj = $this->ResultMapper->findById($args['id']);
        if($result_obj){
            $_toJson['id'] = $result_obj->getId();
            $_toJson['user']['id'] = $result_obj->getUser()->getId();
            $_toJson['user']['name'] = $result_obj->getUser()->getName();
            $_toJson['shot']['howitzer']['id'] = $result_obj->getShot()->getHowitzer()->getId();
            $_toJson['shot']['howitzer']['weight'] = $result_obj->getShot()->getHowitzer()->getWeight();
            $_toJson['shot']['target']['id'] = $result_obj->getShot()->getTarget()->getId();
            $_toJson['shot']['target']['size'] = $result_obj->getShot()->getTarget()->getSize();
            $_toJson['shot']['distance']['id'] = $result_obj->getShot()->getDistance()->getId();
            $_toJson['shot']['distance']['distance'] = $result_obj->getShot()->getDistance()->getDistance();
            $_toJson['shot']['speed']['id'] = $result_obj->getShot()->getSpeed()->getId();
            $_toJson['shot']['speed']['speed'] = $result_obj->getShot()->getSpeed()->getSpeed();
            $_toJson['shot']['angle']['id'] = $result_obj->getShot()->getAngle()->getId();
            $_toJson['shot']['angle']['angle'] = $result_obj->getShot()->getAngle()->getAngle();
            $_toJson['hit'] = $result_obj->getHit();
            $_toJson['impact'] =  $result_obj->getImpact();
        }  
        return $response->withJson($_toJson, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Create new result
 */
$app->post('/results', function (Request $request, Response $response){
    try {
        $decoded = $this->TokenService->decodeToken($request->getHeaders());
        $token_user_id = $decoded['result']->context->user->user_id;
    	$user_id = $request->getParam('user_id');

        if($user_id != $token_user_id) {
            return $response->withJson("User is not valid!", 400);
        }

    	$shot_id = $request->getParam('shot_id');
    	$impact = $request->getParam('impact');
    	$hit = $request->getParam('hit');

        $user = $this->UserMapper->findById($user_id);
        $shot = $this->ShotMapper->findById($shot_id);

        if(!$user) {
        	return $response->withJson("User does not exist!", 400);
        }

        if(!$shot) {
        	return $response->withJson("Shot does not exist!", 400);
        }

        if($hit =! 0 && $hit =! 1) {
        	return $response->withJson("Hit does not exist!", 400);
        }

        if(!$impact) {
        	return $response->withJson("Impact does not exist!", 400);
        }

        $result_id = $this->ResultMapper->insert($shot, $user, $hit, $impact);

        return $response->withJson($result_id, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get top best shotters
 */
$app->get('/top/{limit:[0-9]+}', function (Request $request, Response $response, $args){
    try {
        $top = array();
        $top_arr = $this->ResultMapper->getTopAcurateUsersByLimit($args['limit']);
        if($top_arr){
            foreach ($top_arr as $key => $value) {
                $top[$key]['user']['id'] = $value['user']->getId();
                $top[$key]['user']['username'] = $value['user']->getUsername();
                $top[$key]['shots'] = $value['shots'];
                $top[$key]['avg_closed_target'] = $value['avg_closed_target'];
            }
        }
        return $response->withJson($top, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) );

/*
 * Get total shots
 */
$app->get('/total-shots', function (Request $request, Response $response){
    try {
        $total = array(
        	"total" => count($this->ShotMapper->findAll())
        );
        return $response->withJson($total, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) );

/*
 * Get total users
 */
$app->get('/total-users', function (Request $request, Response $response){
    try {
        $total = array(
        	"total" => count($this->UserMapper->findAll())
        );
        return $response->withJson($total, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) );

/*
 * Get average shot by user
 */
$app->get('/avg-shots', function (Request $request, Response $response){
    try {
        $avg = array(
        	"avg" => number_format(count($this->ShotMapper->findAll())/count($this->UserMapper->findAll()), 2)
        );
        return $response->withJson($avg, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) );

/*
 * Get ranking by user
 */
$app->get('/ranking', function (Request $request, Response $response){
    try {
        $ranking = array();
        $ranking_arr = $this->ResultMapper->getRankingAllUsers();
        if($ranking_arr){
            foreach ($ranking_arr as $key => $value) {
                $ranking[$key]['user']['id'] = $value['user']->getId();
                $ranking[$key]['user']['name'] = $value['user']->getName();
                $ranking[$key]['hits'] = $value['hits'];
            }
        } 
        return $response->withJson($ranking, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) );

/*
 * Get total shots by user
 */
$app->get('/total-shots-by-user/{id:[0-9]+}', function (Request $request, Response $response, $args){
    try {    
        $total = array(
        	"total" => $this->ShotService->getTotalShotByUser($args['id'])
        );
        return $response->withJson($total, 200);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

/*
 * Get calculate impact on target
 */
$app->get('/calculate-trajectoire/{id:[0-9]+}', function (Request $request, Response $response, $args){
	try{
        $_toJson = array();
        $shot = $this->ShotMapper->findById($args['id']);
        if($shot){
            $impact = $this->ShotService->calculateTrajectoire($shot);
            $_toJson['impact'] = $impact;
            $_toJson['user_id'] = (int) $shot->getUser()->getId();
            $_toJson['shot_id'] = (int) $shot->getId();
            $_toJson['hit'] = (boolean) $impact == 0 ? 1 : 0;
            return $response->withJson($_toJson, 200);
        }
        return $response->withJson("Shot does not exist!", 404);
    } catch(PDOException $e) {
    	return $response->withJson($e->getMessage(), 500);
    }
})->add( new restrictionMiddleware($app->getContainer()) ); 

$app->get(
    '/',
    function (Request $request, Response $response) {
        $template = <<<EOT
<!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8"/>
            <title>Slim Framework for PHP 5</title>
            <style>
                html,body,div,span,object,iframe,
                h1,h2,h3,h4,h5,h6,p,blockquote,pre,
                abbr,address,cite,code,
                del,dfn,em,img,ins,kbd,q,samp,
                small,strong,sub,sup,var,
                b,i,
                dl,dt,dd,ol,ul,li,
                fieldset,form,label,legend,
                table,caption,tbody,tfoot,thead,tr,th,td,
                article,aside,canvas,details,figcaption,figure,
                footer,header,hgroup,menu,nav,section,summary,
                time,mark,audio,video{margin:0;padding:0;border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent;}
                body{line-height:1;}
                article,aside,details,figcaption,figure,
                footer,header,hgroup,menu,nav,section{display:block;}
                nav ul{list-style:none;}
                blockquote,q{quotes:none;}
                blockquote:before,blockquote:after,
                q:before,q:after{content:'';content:none;}
                a{margin:0;padding:0;font-size:100%;vertical-align:baseline;background:transparent;}
                ins{background-color:#ff9;color:#000;text-decoration:none;}
                mark{background-color:#ff9;color:#000;font-style:italic;font-weight:bold;}
                del{text-decoration:line-through;}
                abbr[title],dfn[title]{border-bottom:1px dotted;cursor:help;}
                table{border-collapse:collapse;border-spacing:0;}
                hr{display:block;height:1px;border:0;border-top:1px solid #cccccc;margin:1em 0;padding:0;}
                input,select{vertical-align:middle;}
                html{ background: #EDEDED; height: 100%; }
                body{background:#FFF;margin:0 auto;min-height:100%;padding:0 30px;width:440px;color:#666;font:14px/23px Arial,Verdana,sans-serif;}
                h1,h2,h3,p,ul,ol,form,section{margin:0 0 20px 0;}
                h1{color:#333;font-size:20px;}
                h2,h3{color:#333;font-size:14px;}
                h3{margin:0;font-size:12px;font-weight:bold;}
                ul,ol{list-style-position:inside;color:#999;}
                ul{list-style-type:square;}
                code,kbd{background:#EEE;border:1px solid #DDD;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:0 4px;color:#666;font-size:12px;}
                pre{background:#EEE;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:5px 10px;color:#666;font-size:12px;}
                pre code{background:transparent;border:none;padding:0;}
                a{color:#70a23e;}
                header{padding: 30px 0;text-align:center;}
                code {
                    display:block;
                }
            </style>
        </head>
        <body>
            <header>
            </header>
            <h1>Welcome!</h1>
            <p>
                Here is a Howitzer Game application as my sample code.<br>
                <b>How it works</b> : You pick a user, weight of howitzer, distance of target, size of the target, speed, angle of shot and you fire.
                The results are saved in the database and a stats are processed against that.
                <br/><br/
                <b>Demo:</b> <a href="http://ec2-52-90-251-194.compute-1.amazonaws.com/public/">http://ec2-52-90-251-194.compute-1.amazonaws.com/public/</a>
            </p>
            <section>
                <h2>Technologies</h2>
                <ul>
                    <li>Linux(Amazon Web Services Cloud) : Ubuntu 12
                    <li>Apache : Apache</li>
                    <li>Mysql : version 5.5</li>
                    <li>PHP : PHP 5 & Framework Slim</li>
                    <li>HTML / CSS : Bootstrap Library</li>
                    <li>Javascrpit : Straight Javascrpit, Jquery, Handlebars</li>
                </ul>
            </section>
            <section>
                <h1>API</h1>
                <section>
                    <h2>Show Multiple user</h2>
                    <p>Returns json data about a multiple users.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/users</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"user": [{"id":"1","name":"user_1"},{"id":"2","name":"user_2"}]}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single User</h2>
                    <p>Returns json data about a single user.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/users/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id=[integer]`</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{ id : 12, name : "Michael Bloom" }</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create User</h2>
                    <p>Returns json data about user ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/users</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p><i>Required:</i> `name=[alpha numeric]`</p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{user_id: 12}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Get List Howitzer</h2>
                    <p>Returns json data about a multiple howitzers.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/howitzers</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"howitzer": [{"id":"1","weight":"1000"},{"id":"2","weight":"2000"}]}</code></li>
                    <ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single Howitzer</h2>
                    <p>Returns json data about a single howitzer.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/howitzers/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id=[integer]`</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"howitzer": {"id":"1","weight":"1000"}}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create Howitzer</h2>
                    <p>Returns json data about howitzer ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/howitzers</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p><i>Required:</i> `weight = [integer]`</p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{howitzer_id: 13}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Get List Distance</h2>
                    <p>Returns json data about a multiple distances.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/distances</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"distances": [{"id":"1","distance":"1000"},{"id":"2","distance":"2000"}]}</code></li>
                    <ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single Distance</h2>
                    <p>Returns json data about a single distance.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/distances/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id = [integer]`
                    </p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"distance": {"id":"1","distance":"1000"}}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create Distance</h2>
                    <p>Returns json data about distance ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/distances</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p><i>Required:</i> `distance=[alphanumeric]`</p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{distance_id: 16}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Get List Target</h2>
                    <p>Returns json data about a multiple targets.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/targets</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"targets": [{"id":"1","size":"10"},{"id":"2","size":"20"}]}</code></li>
                    <ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single Target</h2>
                    <p>Returns json data about a single target.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/targets/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id=[integer]`
                    </p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"target": {"id":"1","size":"10"}}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create Target</h2>
                    <p>Returns json data about target ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/targets</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p><i>Required:</i> `size=[integer]`</p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{target_id: 16}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Get List Speed</h2>
                    <p>Returns json data about a multiple speeds.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/speeds</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"speeds": [{"id":"1","speed":"10"},{"id":"2","speed":"20"}]}</code></li>
                    <ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single Speed</h2>
                    <p>Returns json data about a single speed.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/speeds/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id=[integer]`
                    </p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"speed": {"id":"1","speed":"10"}}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create Speed</h2>
                    <p>Returns json data about speed ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/speeds</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p><i>Required:</i> `speed=[integer]`</p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{speed_id: 16}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Get List Angle</h2>
                    <p>Returns json data about a multiple angles.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/angles</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"angles": [{"id":"1","angle":"10"},{"id":"2","angle":"20"}]}</code></li>
                    <ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single Angle</h2>
                    <p>Returns json data about a single angle.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/angles/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id=[integer]`
                    </p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"angle": {"id":"1","angle":"10"}}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create Angle</h2>
                    <p>Returns json data about angle ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/angles</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p><i>Required:</i> `angle=[integer]`</p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{angle_id: 16}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Get List Shot</h2>
                    <p>Returns json data about a multiple shots.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/shots</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"shots": [{"id":"1","user":{"id":"1","name":"user_1"},"howitzer":{"id":"1","weight":"1000"},"target":{"id":"1","size":"10"},"distance":{"id":"1","distance":"100"},"speed":{"id":"1","speed":"5"},"angle":{"id":"1","angle":"5"}},{"id":"21","user":{"id":"1","name":"user_1"},"howitzer":{"id":"1","weight":"1000"},"target":{"id":"1","size":"10"},"distance":{"id":"1","distance":"100"},"speed":{"id":"1","speed":"5"},"angle":{"id":"5","angle":"25"}}]}</code></li>
                    <ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single Shot</h2>
                    <p>Returns json data about a single shot.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/shots/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id=[integer]`
                    </p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"shot": {"id":"1","user":{"id":"1","name":"user_1"},"howitzer":{"id":"1","weight":"1000"},"target":{"id":"1","size":"10"},"distance":{"id":"1","distance":"100"},"speed":{"id":"1","speed":"5"},"angle":{"id":"1","angle":"5"}}}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create Shot</h2>
                    <p>Returns json data about shot ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/shots</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p>
                        <i>Required:</i>
                        <ul style="margin-left: 30px;">
                            <li>angle_id = [integer]</li>
                            <li>howitzer_id = [integer]</li>
                            <li>target_id = [integer]</li>
                            <li>distance_id = [integer]</li>
                            <li>speed_id = [integer]</li>
                            <li>user_id = [integer]</li>
                        </ul>
                    </p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"shot_id": 19}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Get List Result</h2>
                    <p>Returns json data about a multiple results.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/results</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"results": [{"id":"1","user":{"id":"1","name":"user_1"},"howitzer":{"id":"1","weight":"1000"},"target":{"id":"1","size":"10"},"distance":{"id":"1","distance":"100"},"speed":{"id":"1","speed":"5"},"angle":{"id":"1","angle":"5"}},{"id":"21","user":{"id":"1","name":"user_1"},"howitzer":{"id":"1","weight":"1000"},"target":{"id":"1","size":"10"},"distance":{"id":"1","distance":"100"},"speed":{"id":"1","speed":"5"},"angle":{"id":"5","angle":"25"}}]}</code></li>
                    <ul>
                </section>
                <hr>
                <section>
                    <h2>Show Single Result</h2>
                    <p>Returns json data about a single shot.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/results/:id</p>
                    <h4>Method</h4>
                    <p>GET</p>
                    <h4>URL Params</h4>
                    <p>
                        <i>Required:</i> `id=[integer]`
                    </p>
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"result": {"id":"1","user":{"id":"1","name":"user_1"},"shot":{"howitzer":{"id":"1","name":"1000"},"target":{"id":"1","size":"10"},"distance":{"id":"1","distance":"100"},"speed":{"id":"1","speed":"5"},"angle":{"id":"1","angle":"5"}},"hit":"1","impact":"0"}}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Create Result</h2>
                    <p>Returns json data about result ID.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/results</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p>
                        <i>Required:</i>
                        <ul style="margin-left: 30px;">
                            <li>user_id = [integer]</li>
                            <li>shot_id = [integer]</li>
                            <li>hit = [integer]</li>
                            <li>impact = [integer]</li>
                        </ul>
                    </p>
                    
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"result_id": 19}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show List Top Best Shotters</h2>
                    <p>Returns json data about a Top Best Shotters.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/top/:limit</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>limit = [integer]</p>
                        
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>[{"user":{"id":"1","name":"user_1"},"hits":"4","avg-closed-target":"88.8800"},{"user":{"id":"5","name":"user_5"},"hits":"0","avg-closed-target":"282.0000"}]</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show Total Shots</h2>
                    <p>Returns json data about a total shotters.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/shots-total</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"total": 31}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show Total Users</h2>
                    <p>Returns json data about a Total users.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/users-total</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"total": 31}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show Average Shot</h2>
                    <p>Returns json data about average shot.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/shots-avg</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"avg": 2.88}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show Ranking by User</h2>
                    <p>Returns json data about ranking by user.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/shots-avg</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>None</p>
                        
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>[{"user":{"id":"1","name":"user_1"},"hits":"4"}]</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show Total Shots by User</h2>
                    <p>Returns json data about total shots by user.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/shots-total-by-user/:id</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>id = [integer]</p>
                        
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"total": 25}</code></li>
                    </ul>
                </section>
                <hr>
                <section>
                    <h2>Show Calculate Impact on Target</h2>
                    <p>Returns json data about calculate impact on target.</p>
                    <h4>URL</h4>
                    <p>http://ec2-52-90-251-194.compute-1.amazonaws.com/calculate-trajectoire/:id</p>
                    <h4>Method</h4>
                    <p>POST</p>
                    <h4>URL Params</h4>
                    <p>id = [integer]</p>
                        
                    <h4>Data Params</h4>
                    <p>None</p>
                    <h4>Success Response:</h4>
                    <ul>
                        <li><b>Code</b>: 200</li>
                        <li><b>Content</b>: <code>{"impact":101.38639426832,"user_id":"1","shot_id":"1","hit":0}</code></li>
                    </ul>
                </section>
            </section>
        </body>
    </html>
EOT;
        echo $template;
    }
);
