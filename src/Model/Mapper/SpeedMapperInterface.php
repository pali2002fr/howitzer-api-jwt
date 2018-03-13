<?php
namespace App\Model\Mapper;
use App\Model\SpeedInterface;

interface SpeedMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(SpeedInterface $speed);
	public function delete($id);
}