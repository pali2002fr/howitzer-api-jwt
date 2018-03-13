<?php
namespace App\Model\Mapper;
use App\Model\UserInterface,
	App\Model\HowitzerInterface,
	App\Model\TargetInterface,
	App\Model\DistanceInterface,
	App\Model\SpeedInterface,
	App\Model\AngleInterface,
	App\Model\ShotInterface;

interface ShotMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(UserInterface $user, HowitzerInterface $howitzer, TargetInterface $target, DistanceInterface $distance, SpeedInterface $speed, AngleInterface $angle);
	public function delete(ShotInterface $shot);
	public function deleteByUser(UserInterface $user);
}