<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/', function () {
	//Test Controller
	$this->get('home', function (Request $request, Response $response, $args) {
		//$m = new UserMapper();
		//var_dump($m->findAll());
	});

	//Root
	$this->get('', function (Request $request, Response $response, $args) {
		$response->withStatus(200);
		$response->getBody()->write('Welcome to Howitzer API (Slim 3.0)');
	});

	//Get User already exists
	$this->get('/users/{username:[a-zA-Z0-9]+}/alreadyexists', function (Request $request, Response $response, $args){
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

	//Register
	$this->post('register', function (Request $request, Response $response, $args){
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

	//Authenticate
	$this->post('login', function (Request $request, Response $response, $args) {
		$response->withStatus(200);
	})->add( new authenticateMiddleware($this->getContainer()) );

	//Users
	$this->group('users', function () {
		$this->get('', function (Request $request, Response $response, $args){
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
		});

		//User
		$this->group('/{userid:[0-9]+}', function (){
			$this->get('', function (Request $request, Response $response, $args){
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

			//Update single user: firstname, lastname
			$this->put('', function (Request $request, Response $response, $args){
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
			});

			//Change password
			$this->put('/update/password', function (Request $request, Response $response, $args){
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
			});

			//Update single user: username
			$this->put('/update/username', function (Request $request, Response $response, $args){
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
			});
			
			//Get result
			$this->group('/results', function (){
				//Get ordered results
			    $this->get('[/{orderby:[a-zA-Z]+}]', function (Request $request, Response $response, $args) {
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

				//Get single result
				$this->get('/{resultid:[0-9]+}', function (Request $request, Response $response, $args){
				    try {
				        $_toJson = array();
				        $result_obj = $this->ResultMapper->findById($args['resultid']);
				        if($result_obj){
				            $_toJson['id'] = $result_obj->getId();
				            $_toJson['user']['id'] = $result_obj->getUser()->getId();
				            $_toJson['user']['username'] = $result_obj->getUser()->getUsername();
        					$_toJson['user']['firstname'] = $result_obj->getUser()->getFirstname();
        					$_toJson['user']['lastname'] = $result_obj->getUser()->getLastname();
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
				});
				
			});

		    

			//Delete single user
			$this->delete('', function (Request $request, Response $response, $args){
			    try {
			        $user = array();
			        $userid = $args['userid'];
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
			});

			//Get total shots by user
			$this->get('/total-shots-by-user', function (Request $request, Response $response, $args){
			    try {    
			        $total = array(
			        	"total" => $this->ShotService->getTotalShotByUser($args['userid'])
			        );
			        return $response->withJson($total, 200);
			    } catch(PDOException $e) {
			    	return $response->withJson($e->getMessage(), 500);
			    }
			});

			//Howitzers
			$this->group('/howitzers', function (){
				//Get howitzers
				$this->get('', function (Request $request, Response $response){
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
				});

				//Get, Update, Delete single howitzer
				$this->group('/{howitzerid:[0-9]+}', function (){
					//Get howitzer
					$this->get('', function (Request $request, Response $response, $args){
					    try {
					        $howitzer = array();
					        $howitzers_obj = $this->HowitzerMapper->findById($args['howitzerid']);
					        if($howitzers_obj){
					            $howitzer['id'] = $howitzers_obj->getId();
					            $howitzer['weight'] = $howitzers_obj->getWeight();
					            return $response->withJson($howitzer, 200);
					        }
					        return $response->withJson("Howitzer does not exist!", 404);
					    } catch(PDOException $e) {
					    	return $response->withJson($e->getMessage(), 500);
					    }
					});

					//Update howitzer
					$this->put('', function (Request $request, Response $response, $args){

					});

					//Delete howitzer
					$this->delete('', function (Request $request, Response $response, $args){
						
					});
				});

				//Post howitzer
				$this->post('', function (Request $request, Response $response){
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
				});
			});

			//Distances
			$this->group('/distances', function (){
				//Get distances
				$this->get('', function ($request, $response){
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
				});

				//Get, Update, Delete single distance
				$this->group('/{distanceid:[0-9]+}', function (){
					//Get distance
					$this->get('', function (Request $request, Response $response, $args){
					    try {
					        $distance = array();
					        $distance_obj = $this->DistanceMapper->findById($args['distanceid']);
					        if($distance_obj){
					            $distance['id'] = $distance_obj->getId();
					            $distance['distance'] = $distance_obj->getDistance();
					            return $response->withJson($distance);
					        }
					        return $response->withJson("Distance does not exist!", 404);
					    } catch(PDOException $e) {
					    	return $response->withJson($e->getMessage(), 500);
					    }
					});

					//Update distance
					$this->put('', function (Request $request, Response $response, $args){

					});

					//Delete distance
					$this->delete('', function (Request $request, Response $response, $args){
						
					});
				});

				//Post distance
				$this->post('', function (Request $request, Response $response, $args){
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
				});
			});
			
			//Target
			$this->group('/targets', function (){
				//Get targets
				$this->get('', function ($request, $response){
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
				});

				//Get, Update, Delete single target
				$this->group('/{targetid:[0-9]+}', function (){
					//Get target
					$this->get('', function (Request $request, Response $response, $args){
					    try {
					        $target = array();
					        $target_obj = $this->TargetMapper->findById($args['targetid']);
					        if($target_obj){
					            $target['id'] = $target_obj->getId();
					            $target['size'] = $target_obj->getSize();
					            return $response->withJson($target);
					        }
					        return $response->withJson("Target does not exist!", 404);
					    } catch(PDOException $e) {
					    	return $response->withJson($e->getMessage(), 500);
					    }
					});

					//Update target
					$this->put('', function (Request $request, Response $response, $args){

					});

					//Delete target
					$this->delete('', function (Request $request, Response $response, $args){
						
					});
				});

				//Post target
				$this->post('', function (Request $request, Response $response){
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
				});
			});

			$this->group('/speeds', function (){
				//Get speeds
				$this->get('', function (Request $request, Response $response){
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
				});

				//Get, Update, Delete single speed
				$this->group('/{speedid:[0-9]+}', function (){
					//Get speed
					$this->get('', function (Request $request, Response $response, $args){
					    try {
					        $speed = array();
					        $speed_obj = $this->SpeedMapper->findById($args['speedid']);
					        if($speed_obj){
					            $speed['id'] = $speed_obj->getId();
					            $speed['speed'] = $speed_obj->getSpeed();
					            return $response->withJson($speed, 200);
					        }
					        return $response->withJson("Speed does not exist!", 404);
					    } catch(PDOException $e) {
					    	return $response->withJson($e->getMessage(), 500);
					    }
					});

					//Update speed
					$this->put('', function (Request $request, Response $response, $args){

					});

					//Delete speed
					$this->delete('', function (Request $request, Response $response, $args){
						
					});
				});
				
				// Post speed
				$this->post('', function (Request $request, Response $response){
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
				            return $response->withJson($speed_id, 200);
				        }
				        return $response->withJson("Speed already exists!", 400);
				    } catch(PDOException $e) {
				    	return $response->withJson($e->getMessage(), 500);
				    }
				});
			});
			
			//Angle
			$this->group('/angles', function (){
				//Get angles
				$this->get('', function ($request, $response){
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
				});

				//Get, Update, Delete single angle
				$this->group('/{angleid:[0-9]+}', function (){
					//Get angle
					$this->get('', function (Request $request, Response $response, $args){
					    try {
					        $angle = array();
					        $angle_obj = $this->AngleMapper->findById($args['angleid']);
					        if($angle_obj){
					            $angle['id'] = $angle_obj->getId();
					            $angle['angle'] = $angle_obj->getAngle();
					            return $response->withJson($angle, 200);
					        }
					        return $response->withJson("Angle does not exist!", 404);
					    } catch(PDOException $e) {
					    	return $response->withJson($e->getMessage(), 500);
					    }
					});

					//Update angle
					$this->put('', function (Request $request, Response $response, $args){

					});

					//Delete angle
					$this->delete('', function (Request $request, Response $response, $args){
						
					});
				});
				

				//Post angle
				$this->post('', function (Request $request, Response $response, $args){
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
				});
			});

			//Shot
			$this->group('/shots', function (){
				//Get shots
				$this->get('', function ($request, $response){
				    try {
				        $_toJson = array();
				        $shots_obj = $this->ShotMapper->findAll();
				        if($shots_obj){
				            foreach ($shots_obj as $key => $value) {
				                $_toJson[$key]['id'] = $value->getId();
				                $_toJson[$key]['user']['id'] = $value->getUser()->getId();
				                $_toJson[$key]['user']['username'] = $value->getUser()->getUsername();
                				$_toJson[$key]['user']['firstname'] = $value->getUser()->getFirstname();
                				$_toJson[$key]['user']['lastname'] = $value->getUser()->getLastname();
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
				});

				//Create new shot
				$this->post('', function (Request $request, Response $response){
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
				});

				//Get shot
				$this->group('/{shotid:[0-9]+}', function (){
					$this->get('', function (Request $request, Response $response, $args){
					    try {
					        $_toJson = array();
					        $shot_obj = $this->ShotMapper->findById($args['shotid']);
					        if($shot_obj){
					            $_toJson['id'] = $shot_obj->getId();
					            $_toJson['user']['id'] = $shot_obj->getUser()->getId();
					            $_toJson['user']['username'] = $shot_obj->getUser()->getUsername();
                				$_toJson['user']['firstname'] = $shot_obj->getUser()->getFirstname();
                				$_toJson['user']['lastname'] = $shot_obj->getUser()->getLastname();
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
					});

					//Get calculate impact on target
					$this->get('/calculate-trajectoire', function (Request $request, Response $response, $args){
						try{
					        $_toJson = array();
					        $shot = $this->ShotMapper->findById($args['shotid']);
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
					});

					//Result
					$this->group('/result', function(){
						//Get results
						$this->get('', function (Request $request, Response $response, $args){
						    try {
						        $_toJson = array();
						        $shot_id = $args['shotid'];
								$conditions = [
				                    	"id_shot" => [
				                        "value" => $shot_id,
				                        "operator" => "="
				                    ]
				                ];

						        $result_obj = $this->ResultMapper->findAll($conditions);
						        if($result_obj){
						            $_toJson['id'] = $result_obj[0]->getId();
						            $_toJson['user']['id'] = $result_obj[0]->getUser()->getId();
						            $_toJson['user']['username'] = $result_obj[0]->getUser()->getUsername();
		        					$_toJson['user']['firstname'] = $result_obj[0]->getUser()->getFirstname();
		        					$_toJson['user']['lastname'] = $result_obj[0]->getUser()->getLastname();
						            $_toJson['shot']['howitzer']['id'] = $result_obj[0]->getShot()->getHowitzer()->getId();
						            $_toJson['shot']['howitzer']['weight'] = $result_obj[0]->getShot()->getHowitzer()->getWeight();
						            $_toJson['shot']['target']['id'] = $result_obj[0]->getShot()->getTarget()->getId();
						            $_toJson['shot']['target']['size'] = $result_obj[0]->getShot()->getTarget()->getSize();
						            $_toJson['shot']['distance']['id'] = $result_obj[0]->getShot()->getDistance()->getId();
						            $_toJson['shot']['distance']['distance'] = $result_obj[0]->getShot()->getDistance()->getDistance();
						            $_toJson['shot']['speed']['id'] = $result_obj[0]->getShot()->getSpeed()->getId();
						            $_toJson['shot']['speed']['speed'] = $result_obj[0]->getShot()->getSpeed()->getSpeed();
						            $_toJson['shot']['angle']['id'] = $result_obj[0]->getShot()->getAngle()->getId();
						            $_toJson['shot']['angle']['angle'] = $result_obj[0]->getShot()->getAngle()->getAngle();
						            $_toJson['hit'] = $result_obj[0]->getHit();
						            $_toJson['impact'] =  $result_obj[0]->getImpact();
						        }  
						        return $response->withJson($_toJson, 200);
						    } catch(PDOException $e) {
						    	return $response->withJson($e->getMessage(), 500);
						    }
						});

						// Post result
						$this->post('', function (Request $request, Response $response){
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

						        $result_id = $this->ResultMapper->insert($shot, $user, (int)$hit, $impact);

						        return $response->withJson($result_id, 200);
						    } catch(PDOException $e) {
						    	return $response->withJson($e->getMessage(), 500);
						    }
						});
					});
				});
			});
		});
	})->add( new restrictionMiddleware($this->getContainer()) );

	//Get ranking
	$this->get('ranking', function ($request, $response){
	    try {
	        $ranking = array();
	        $ranking_arr = $this->ResultMapper->getRankingAllUsers();
	        if($ranking_arr){
	            foreach ($ranking_arr as $key => $value) {
	                $ranking[$key]['user']['id'] = $value['user']->getId();
	                $ranking[$key]['user']['username'] = $value->getUser()->getUsername();
                	$ranking[$key]['user']['firstname'] = $value->getUser()->getFirstname();
                	$ranking[$key]['user']['lastname'] = $value->getUser()->getLastname();
	                $ranking[$key]['hits'] = $value['hits'];
	            }
	        } 
	        return $response->withJson($ranking, 200);
	    } catch(PDOException $e) {
	    	return $response->withJson($e->getMessage(), 500);
	    }
	});

	//Get average shot
	$this->get('avg-shots', function ($request, $response){
	    try {
	        $avg = array(
	        	"avg" => number_format(count($this->ShotMapper->findAll())/count($this->UserMapper->findAll()), 2)
	        );
	        return $response->withJson($avg, 200);
	    } catch(PDOException $e) {
	    	return $response->withJson($e->getMessage(), 500);
	    }
	});

	//Get total users
	$this->get('total-users', function ($request, $response){
	    try {
	        $total = array(
	        	"total" => count($this->UserMapper->findAll())
	        );
	        return $response->withJson($total, 200);
	    } catch(PDOException $e) {
	    	return $response->withJson($e->getMessage(), 500);
	    }
	});

	//Get top best shotters
	$this->get('top/{limit:[0-9]+}', function (Request $request, Response $response, $args){
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
	});

	//Get total shots
	$this->get('total-shots', function ($request, $response){
	    try {
	        $total = array(
	        	"total" => count($this->ShotMapper->findAll())
	        );
	        return $response->withJson($total, 200);
	    } catch(PDOException $e) {
	    	return $response->withJson($e->getMessage(), 500);
	    }
	});
});
	





