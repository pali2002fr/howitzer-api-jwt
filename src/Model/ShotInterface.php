<?php
namespace App\Model;

interface ShotInterface
{
 	public function setId($id);
 	public function getId();

 	public function setUser( UserInterface $user);
 	public function getUser();
 	
 	public function setHowitzer( HowitzerInterface $howitzer);
 	public function getHowitzer();
 	
 	public function setTarget( TargetInterface $target);
 	public function getTarget();
 	
 	public function setDistance( DistanceInterface $distance);
 	public function getDistance();
 	
 	public function setSpeed( SpeedInterface $speed);
 	public function getSpeed();
 	
 	public function setAngle( AngleInterface $angle);
 	public function getAngle();

	public function setCreated_date($created_date);
 	public function getCreated_date();

 	
}