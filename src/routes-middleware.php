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

	//Register
	$this->post('register', function (Request $request, Response $response, $args){
	    try {
	    	$fisrtname = $request->getParam('fisrtname');
	    	$lastname = $request->getParam('lastname');
	    	$username = $request->getParam('username');
	    	$password = $request->getParam('password');

	    	$filter = array(
	        	'username' => array(
	        		'value' => $username,
	        		'operator' => '='
	        	)
	        );

	        $user_exists = $this->UserMapper->findAll($filter);
	        if(!$user_exists){
	            $user = new User($firstname, $lastname, $username, $password);

	            $result = array();
	            $result['id'] = $this->UserMapper->insert( $user );

	            return $response->withJson($result, 201);
	        } else {
	            $warning = array(
	        		"warning" => "User already exists!"
	        	);
	        	return $response->withJson($warning, 302);
	        }
	    } catch(PDOException $e) {
	        $error = array(
	    		"error" => $e->getMessage()
	    	);
	    	return $response->withJson($error);
	    }
	});

	//Authenticate
	$this->post('login', function (Request $request, Response $response, $args) {
		$response->withStatus(200);
	//})->add( new authenticateMiddleware($this->getContainer()) );
	});	

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
			        return $response->withJson($users, 200);
		        } else {
		        	$warning = array(
		        		"warning" => "No user!"
		        	);
		        	return $response->withJson($warning, 204);
		        } 
		    } catch(PDOException $e) {
		    	$error = array(
		    		"error" => $e->getMessage()
		    	);
		    	return $response->withJson($error, 500);
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
			        } else {
			            $warning = array(
			        		"warning" => "User does not exist!"
			        	);
			        	return $response->withJson($warning, 404);
			        }

			    } catch(PDOException $e) {
			        $error = array(
			    		"error" => $e->getMessage()
			    	);
			    	return $response->withJson($error, 500);
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
			        $error = array(
			    		"error" => $e->getMessage()
			    	);
			    	return $response->withJson($error, 500);
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
				            return $response->withJson($howitzers, 200);
				        } else {
				            $warning = array(
				        		"warning" => "No howitzer!"
				        	);
				        	return $response->withJson($warning, 204);
				        } 
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Get howitzer
				$this->get('/{howitzerid:[0-9]+}', function (Request $request, Response $response, $args){
				    try {
				        $howitzer = array();
				        $howitzers_obj = $this->HowitzerMapper->findById($args['howitzerid']);
				        if($howitzers_obj){
				            $howitzer['id'] = $howitzers_obj->getId();
				            $howitzer['weight'] = $howitzers_obj->getWeight();
				            return $response->withJson($howitzer, 200);
				        } else {
				            $warning = array(
				        		"warning" => "Howitzer does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        } 
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Post howitzer
				$this->post('', function (Request $request, Response $response){
				    try {
				    	$weight = $request->getParam('weight');

				    	$filter = array(
				        	'weight' => array(
				        		'value' => $weight,
				        		'operator' => '='
				        	)
				        );

				        $howitzer_exists = $this->HowitzerMapper->findAll($filter);
				        if(!$howitzer_exists){
				            $howitzer = new Howitzer($weight);

				            $result = array();
				            $result['id'] = $this->HowitzerMapper->insert( $howitzer );

				            return $response->withJson($result, 201);
				        } else {
				            $warning = array(
				        		"warning" => "Howitzer already exists!"
				        	);
				        	return $response->withJson($warning, 302);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error);
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
				            return $response->withJson($distances, 200);
				        } else {
				            $warning = array(
				        		"warning" => "No distance!"
				        	);
				        	return $response->withJson($warning, 204);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Get distance
				$this->get('/{distanceid:[0-9]+}', function (Request $request, Response $response, $args){
				    try {
				        $distance = array();
				        $distance_obj = $this->DistanceMapper->findById($args['distanceid']);
				        if($distance_obj){
				            $distance['id'] = $distance_obj->getId();
				            $distance['distance'] = $distance_obj->getDistance();
				            return $response->withJson($distance, 200);
				        } else {
				            $warning = array(
				        		"warning" => "Distance does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Post distance
				$this->post('', function (Request $request, Response $response, $args){
				    try {
				    	$distance = $request->getParam('distance');

				    	$filter = array(
				        	'distance' => array(
				        		'value' => $distance,
				        		'operator' => '='
				        	)
				        );

				        $distance_exists = $this->DistanceMapper->findAll($filter);
				        if(!$distance_exists){
				            $distance = new Distance( $distance );

				            $result = array();
				            $result['id'] = $distanceMapper->insert( $distance );

				            return $response->withJson($result, 201);
				        } else {
				            $warning = array(
				        		"warning" => "Distance already exists!"
				        	);
				        	return $response->withJson($warning, 302);
				        } 
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
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
				            return $response->withJson($targets, 200);
				        } else {
				            $warning = array(
				        		"warning" => "No target!"
				        	);
				        	return $response->withJson($warning, 204);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Get target
				$this->get('/{targetid:[0-9]+}', function (Request $request, Response $response, $args){
				    try {
				        $target = array();
				        $target_obj = $this->TargetMapper->findById($args['targetid']);
				        if($target_obj){
				            $target['id'] = $target_obj->getId();
				            $target['size'] = $target_obj->getSize();
				            return $response->withJson($target, 200);
				        } else {
				            $warning = array(
				        		"warning" => "Target does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Post target
				$this->post('', function (Request $request, Response $response){
				    try {
				    	$size = $request->getParam('size');

				    	$filter = array(
				        	'target' => array(
				        		'value' => $target,
				        		'operator' => '='
				        	)
				        );

				        $target_exists = $this->TargetMapper->findAll($filter);
				        if(!$target_exists){
				            $target = new Target( $size );

				            $result = array();
				            $result['id'] = $targetMapper->insert( $target );
				            return $response->withJson($result, 201);
				        } else {
				            $warning = array(
				        		"warning" => "Target already exists!"
				        	);
				        	return $response->withJson($warning, 302);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
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
				            return $response->withJson($speeds, 200);
				        } else {
				            $warning = array(
				        		"warning" => "No speed!"
				        	);
				        	return $response->withJson($warning, 204);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Get speed
				$this->get('/{speedid:[0-9]+}', function (Request $request, Response $response, $args){
				    try {
				        $speed = array();
				        $speed_obj = $this->SpeedMapper->findById($args['speedid']);
				        if($speed_obj){
				            $speed['id'] = $speed_obj->getId();
				            $speed['speed'] = $speed_obj->getSpeed();
				            return $response->withJson($speed, 200);
				        } else {
				            $warning = array(
				        		"warning" => "Speed does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				// Post speed
				$this->post('', function (Request $request, Response $response){
				    try {
				    	$speed = $request->getParam('speed');

				    	$filter = array(
				        	'speed' => array(
				        		'value' => $speed,
				        		'operator' => '='
				        	)
				        );

				        $speed_exists = $this->SpeedMapper->findAll($filter);
				        if(!$speed_exists){
				            $speed = new Speed($speed);
				            
				            $result = array();
				            $result['id'] = $speedMapper->insert( $speed );

				            return $response->withJson($result, 201);
				        } else {
				            $warning = array(
				        		"warning" => "Speed already exists!"
				        	);
				        	return $response->withJson($warning, 302);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
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
				            return $response->withJson($angles, 200);
				        } else {
				            $warning = array(
				        		"warning" => "No angle!"
				        	);
				        	return $response->withJson($warning, 204);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Get angle
				$this->get('/{angleid:[0-9]+}', function (Request $request, Response $response, $args){
				    try {
				        $angle = array();
				        $angle_obj = $this->AngleMapper->findById($args['angleid']);
				        if($angle_obj){
				            $angle['id'] = $angle_obj->getId();
				            $angle['angle'] = $angle_obj->getAngle();
				            return $response->withJson($angle, 200);
				        } else {
				            $warning = array(
				        		"warning" => "Angle does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
				    }
				});

				//Post angle
				$this->post('', function (Request $request, Response $response, $args){
				    try {
				    	$angle = $request->getParam('angle');

				    	$filter = array(
				        	'angle' => array(
				        		'value' => $angle,
				        		'operator' => '='
				        	)
				        );

				        $angle_exists = $this->AngleMapper->findAll($filter);
				        if(!$angle_exists){
				            $angle = new Angle($angle);

				            $result = array();
				            $result['id'] = $angleMapper->insert( $angle );

				            return $response->withJson($result, 201);
				        } else {
				            $warning = array(
				        		"warning" => "Angle already exists!"
				        	);
				        	return $response->withJson($warning, 302);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
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
				            return $response->withJson($_toJson, 200);
				        } else {
				            $warning = array(
				        		"warning" => "No shot!"
				        	);
				        	return $response->withJson($warning, 204);
				        }
				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error, 500);
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
					        } else {
					            $warning = array(
					        		"warning" => "Shot does not exist!"
					        	);
					        	return $response->withJson($warning, 404);
					        }
					    } catch(PDOException $e) {
					        $error = array(
					    		"error" => $e->getMessage()
					    	);
					    	return $response->withJson($error, 500);
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
					        } else {
					            $warning = array(
					        		"warning" => "Shot does not exist!"
					        	);
					        	return $response->withJson($warning, 204);
					        }
					    } catch(PDOException $e) {
					        $error = array(
					    		"error" => $e->getMessage()
					    	);
					    	return $response->withJson($error, 500);
					    }
					});

					//Result
					$this->group('/results', function(){
						//Get results
						$this->get('', function (Request $request, Response $response, $args){
						    try {
						        $_toJson = array();
						        $results_obj = $this->ResultMapper->findAll();
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
						                $_toJson[$key]['hit'] = $value->getHit();
						                $_toJson[$key]['impact'] =  $value->getImpact();
						            };

						            return $response->withJson($_toJson, 200);
						        } else {
						            $warning = array(
						        		"warning" => "No result!"
						        	);
						        	return $response->withJson($warning, 204);
						        }
						    } catch(PDOException $e) {
						        $error = array(
						    		"error" => $e->getMessage()
						    	);
						    	return $response->withJson($error, 500);
						    }
						});

						//Get result
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

						            return $response->withJson($_toJson);
						        } else {
						            $warning = array(
						        		"warning" => "Result does not exist!"
						        	);
						        	return $response->withJson($warning, 404);
						        }
						    } catch(PDOException $e) {
						        $error = array(
						    		"error" => $e->getMessage()
						    	);
						    	return $response->withJson($error, 500);
						    }
						});

						// Post result
						$this->post('', function (Request $request, Response $response){
						    try {
						    	$user_id = $request->getParam('user_id');
						    	$shot_id = $request->getParam('shot_id');
						    	$impact = $request->getParam('impact');
						    	$hit = $request->getParam('hit');

						        $user = $this->UserMapper->findById($user_id);
						        $shot = $this->ShotMapper->findById($shot_id);

						        if(!$user) {
						            $warning = array(
						        		"warning" => "User does not exist!"
						        	);
						        	return $response->withJson($warning, 404);
						        }

						        if(!$shot) {
						            $warning = array(
						        		"warning" => "Shot does not exist!"
						        	);
						        	return $response->withJson($warning, 404);
						        }

						        if($hit =! 0 && $hit =! 1) {
						            $warning = array(
						        		"warning" => "Hit does not exist!"
						        	);
						        	return $response->withJson($warning, 501);
						        }

						        if(!is_float($impact)) {
						            $warning = array(
						        		"warning" => "Impact does not exist!"
						        	);
						        	return $response->withJson($warning, 501);
						        }

						        $result = array();
						        $result['id'] = $this->ResultMapper->insert($shot, $user, $hit, $impact);

						        return $response->$response->withJson($result, 201);
						    } catch(PDOException $e) {
						        $error = array(
						    		"error" => $e->getMessage()
						    	);
						    	return $response->withJson($error, 500);
						    }
						});
					});
				});

				//Post shot
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
				            $warning = array(
				        		"warning" => "User does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				        if(!$howitzer) {
				            $warning = array(
				        		"warning" => "Howitzer does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				        if(!$target) {
				            $warning = array(
				        		"warning" => "Target does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				        if(!$distance) {
				            $warning = array(
				        		"warning" => "Distance does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				        if(!$speed) {
				            $warning = array(
				        		"warning" => "Speed does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }
				        if(!$angle) {
				            $warning = array(
				        		"warning" => "Angle does not exist!"
				        	);
				        	return $response->withJson($warning, 404);
				        }

				        $result = array();
				        $result['id'] = $this->ShotMapper->insert( 
				            $user, 
				            $howitzer, 
				            $target, 
				            $distance, 
				            $speed, 
				            $angle
				        );
				        
				        return $response->withJson($result, 201);

				    } catch(PDOException $e) {
				        $error = array(
				    		"error" => $e->getMessage()
				    	);
				    	return $response->withJson($error);
				    }
				});
			});
		});
	//})->add( new restrictionMiddleware($this->getContainer()) );
	});

	//Get ranking
	$this->get('ranking', function ($request, $response){
	    try {
	        $ranking = array();
	        $ranking_arr = $this->ResultMapper->getRankingAllUsers();
	        if($ranking_arr){
	            foreach ($ranking_arr as $key => $value) {
	                $ranking[$key]['user']['id'] = $value['user']->getId();
	                $ranking[$key]['user']['username'] = $value['user']->getUsername();
				    $ranking[$key]['user']['firstname'] = $value['user']->getFirstname();
				    $ranking[$key]['user']['lastname'] = $value['user']->getLastname();
	                $ranking[$key]['hits'] = $value['hits'];
	            }
	            return $response->withJson($ranking, 200);
	        } else {
	            $warning = array(
	        		"warning" => "No ranking!"
	        	);
	        	return $response->withJson($warning, 204);
	        }
	    } catch(PDOException $e) {
	        $error = array(
	    		"error" => $e->getMessage()
	    	);
	    	return $response->withJson($error, 500);
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
	        $error = array(
	    		"error" => $e->getMessage()
	    	);
	    	return $response->withJson($error, 500);
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
	        $error = array(
	    		"error" => $e->getMessage()
	    	);
	    	return $response->withJson($error, 500);
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
				    $top[$key]['user']['firstname'] = $value['user']->getFirstname();
				    $top[$key]['user']['lastname'] = $value['user']->getLastname();
	                $top[$key]['hits'] = $value['hits'];
	                $top[$key]['avg_closed_target'] = $value['avg_closed_target'];
	            }
	            return $response->withJson($top, 200);
	        } else {
	            $warning = array(
	        		"warning" => "No top!"
	        	);
	        	return $response->withJson($warning, 204);
	        }
	    } catch(PDOException $e) {
	        $error = array(
	    		"error" => $e->getMessage()
	    	);
	    	return $response->withJson($error, 500);
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
	        $error = array(
	    		"error" => $e->getMessage()
	    	);
	    	return $response->withJson($error, 500);
	    }
	});
});
	





