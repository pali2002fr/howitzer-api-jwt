<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\TokenInterface,
	App\Model\UserInterface,
	App\Model\Token,
	App\Model\User;
	
class TokenMapper extends AbstractDataMapper implements TokenMapperInterface {
	protected $entityTable = "token";
	protected $userMapper;

	public function __construct(DatabaseAdapterInterface $adapter, 
								UserMapperInterface $userMapper){
		$this->userMapper = $userMapper;
		parent::__construct($adapter);
	}
	
	public function insert(TokenInterface $token){
		return $this->adapter->insert(
			$this->entityTable,
			array(
				'token' => $token->getToken(),
				'user_id' => $token->getUser()->getId(),
				'created_date' => date("Y-m-d H:i:s"),
				'expiration_date' => $token->getExpiration_date()
			)
		);
	}

	public function update(TokenInterface $token){
		return $this->adapter->update(
			$this->entityTable,
			array(
				'token' => $token->getToken(),
				'user_id' => $token->getUser()->getId(),
				'created_date' => $token->getCreated_date(),
				'expiration_date' => $token->getExpiration_date()
			),
			"id = " . $token->getId()
		);
	}
	
	public function delete(TokenInterface $token){
		$id = $token->getId();
		return $this->adapter->delete($this->entityTable, "id = $id");		
	}

	public function deleteByUser(UserInterface $user){
		$id = $user->getId();
		return $this->adapter->delete($this->entityTable, "user_id = $id");		
	}
	
	protected function createEntity(array $row){
		$user = $this->userMapper->findById($row["user_id"]);
		$token = new Token($row['token'], $user, $row['created_date'], $row['expiration_date']);
		$token->setId($row['id']);
		return $token;
	}
}