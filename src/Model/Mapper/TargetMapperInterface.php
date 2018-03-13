<?php
namespace App\Model\Mapper;
use App\Model\TargetInterface;

interface TargetMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(TargetInterface $target);
	public function delete($id);
}