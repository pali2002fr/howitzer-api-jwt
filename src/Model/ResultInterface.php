<?php
namespace App\Model;

interface ResultInterface
{
 	public function setId($id);
 	public function getId();

 	public function setShot( ShotInterface $shot);
 	public function getShot();
 	
 	public function setUser( UserInterface $user);
 	public function getUser();
 	
 	public function setHit($hit);
 	public function getHit();
 	
 	public function setImpact($impact);
 	public function getImpact();
}