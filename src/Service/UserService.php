<?php
namespace App\Service;
use App\Model\Mapper\UserMapperInterface;

class UserService {
	protected $userMapper;
	public function __construct(UserMapperInterface $userMapper){
		$this->userMapper = $userMapper;
	}
	public function getTotalAllUser(){
		return count($this->userMapper->findAll());
	}
}