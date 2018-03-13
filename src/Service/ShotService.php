<?php
namespace App\Service;
use App\Model\Mapper\ShotMapperInterface,
	App\Model\ShotInterface;

class ShotService {
	protected $shotMapper;
	
	public function __construct(ShotMapperInterface $shotMapper) {
		$this->shotMapper = $shotMapper;
	}
	
	public function isHit(ShotInterface $shot){
		$impact = $this->calculateTrajectoire($shot);
    	if(($impact >= ($shot->getDistance()->getDistance() - ($shot->getTarget()->getSize()/2))) && ($impact <= ($shot->getDistance()->getDistance() + ($shot->getTarget()->getSize()/2)))){
     		return true;
     	} else {
     		return false;
     	}
	}
	
	public function calculateTrajectoire(ShotInterface $shot){
		$a = $shot->getAngle()->getAngle();
     	$v0 = $shot->getSpeed()->getSpeed();
    	$m = $shot->getHowitzer()->getWeight();
     	$g = 9.81;
     	$y = 0;		
     	$v0y = $v0 * sin($a);
     	$v0x = $v0 * cos($a);
     	$r = (2 * $v0x * $v0y) / $g;
     	return abs($shot->getDistance()->getDistance() - $r);
	}
	
	public function getTotalShotByUser($id){
		$conditions = array(
			'id_user' => array(
				'value' => $id,
				'operator' => '='
			)
		);
		return count($this->shotMapper->findAll($conditions));
	}
}