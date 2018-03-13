<?php
namespace App\Model\Mapper;
use App\Model\AngleInterface;

interface AngleMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(AngleInterface $angle);
	public function delete($id);
}