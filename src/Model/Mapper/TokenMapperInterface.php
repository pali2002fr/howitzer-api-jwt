<?php
namespace App\Model\Mapper;
use App\Model\TokenInterface,
	App\Model\UserInterface;

interface TokenMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(TokenInterface $token);
	public function update(TokenInterface $token);
	public function delete(TokenInterface $token);
	public function deleteByUser(UserInterface $user);
}