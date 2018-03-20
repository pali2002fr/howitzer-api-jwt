<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\ResultInterface,
	App\Model\ShotInterface,
	App\Model\UserInterface,
	App\Model\Result,
	App\Model\Shot,
	App\Model\User;
	
class ResultMapper extends AbstractDataMapper implements ResultMapperInterface {
	protected $userMapper;
	protected $shotMapper;
	protected $entityTable = "result";
	
	public function __construct(DatabaseAdapterInterface $adapter, 
								UserMapperInterface $userMapper,
								ShotMapperInterface $shotMapper
	){
		$this->userMapper = $userMapper;
		$this->shotMapper = $shotMapper;
		parent::__construct($adapter);
	}

	public function insert(ShotInterface $shot, UserInterface $user, $hit, $impact){
		return $this->adapter->insert(
			$this->entityTable,
			array(
				'id_shot' => $shot->getId(),
				'id_user' => $user->getId(),
				'hit' => $hit,
				'impact' => $impact,
				'created_date' => date("Y-m-d H:i:s")
			)
		);
	}
	
	public function delete(ResultInterface $result){
		$id = $result->getId();
		return $this->adapter->delete($this->entityTable, "id = $id");
	}

	public function deleteByUser(UserInterface $user){
			$id = $user->getId();
			return $this->adapter->delete($this->entityTable, "id_user = $id");
	}
	
	public function getTopAcurateUsersByLimit($number = 5){
		$sql = "SELECT id_user, count(*) as 'shots', AVG(impact) as 'avg'
								FROM result
								GROUP BY  id_user
								ORDER BY avg, shots ASC
								LIMIT " . $number;

		$this->adapter->prepare($sql)->execute();
		$rows = $this->adapter->fetchAll();
		if(!$rows){
			return array();
		}
		foreach($rows as $row){
			$user = $this->userMapper->findById($row['id_user']);
			$return[] = array(
								'user' => $user,
								'shots' => $row['shots'],
								'avg_closed_target' => $row['avg']
						);
		}
		return $return;
	}
	
	public function getRankingAllUsers(){
		$sql = "SELECT id_user, sum(hit) as 'hits'
				FROM result
				GROUP BY  id_user
				ORDER BY hits DESC";

		$this->adapter->prepare($sql)->execute();
		$rows = $this->adapter->fetchAll();
		foreach($rows as $row){
			$user = $this->userMapper->findById($row['id_user']);
			$return[] = array(
								'user' => $user,
								'hits' => $row['hits']
						);
		}
		return $return;
	}
	
	protected function createEntity(array $row){
		$user = $this->userMapper->findById($row["id_user"]);
		$shot = $this->shotMapper->findById($row["id_shot"]);
		$result = new Result($shot, $user, $row['hit'], $row['impact']);
		$result->setId($row['id']);
		return $result;
	}
}

