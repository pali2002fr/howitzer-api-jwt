<?php
namespace App\Model\Mapper;
use App\Model\DistanceInterface;

interface DistanceMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(DistanceInterface $distance);
	public function delete($id);
}