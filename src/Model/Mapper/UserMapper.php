<?php

namespace App\Model\Mapper;

use App\Library\Database\DatabaseAdapterInterface,
	App\Model\UserInterface,
	App\Model\User;
	
class UserMapper extends AbstractDataMapper implements UserMapperInterface {
	protected $entityTable = "user";

	public function insert(UserInterface $user){
		$user->id = $this->adapter->insert(
			$this->entityTable,
			array(
				'firstname' => $user->getFirstname(),
				'lastname' => $user->getLastname(),
				'username' => $user->getUsername(),
				'password' => $user->getPassword(),
				'created_date' => date("Y-m-d H:i:s")
			)
		);
		return $user->id;
	}

	public function update(UserInterface $user){
		return $this->adapter->update(
			$this->entityTable,
			array(
				'firstname' => $user->getFirstname(),
				'lastname' => $user->getLastname(),
				'username' => $user->getUsername(),
				'password' => $user->getPassword()
			),
			"id = " . $user->getId()
		);
	}
	
	public function delete(UserInterface $user){
		$id = $user->getId();
		return $this->adapter->delete($this->entityTable, "id = $id");
	}

	public function findByUsername($username){

		$this->adapter->select(
			$this->entityTable, 
			array('username' => array(
					'value' => $username,
					'operator' => '='
				)
			)
		);

		if(!$row = $this->adapter->fetch()){
			return null;
		}
		return $this->createEntity($row);
	}
	
	protected function createEntity(array $row){
		$user = new User($row['firstname'], $row['lastname'], $row['username'], $row['password']);
		$user->setId($row['id']);
		return $user ;
	}
}