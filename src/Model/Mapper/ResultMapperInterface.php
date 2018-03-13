<?php
namespace App\Model\Mapper;
use App\Model\ShotInterface,
	App\Model\UserInterface,
	App\Model\ResultInterface;

interface ResultMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(ShotInterface $shot, UserInterface $user, $hit, $impact);
	public function delete(ResultInterface $result);
	public function deleteByUser(UserInterface $user);
}