<?php
namespace App\Model\Mapper;
use App\Model\UserInterface;

interface UserMapperInterface{
	public function findById($id);
	public function findByUsername($username);
	public function findAll(array $conditions = array());
	
	public function insert(UserInterface $user);
	public function update(UserInterface $user);
	public function delete(UserInterface $user);
}