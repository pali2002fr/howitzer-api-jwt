<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\UserInterface,
	App\Model\HowitzerInterface,
	App\Model\TargetInterface,
	App\Model\DistanceInterface,
	App\Model\SpeedInterface,
	App\Model\AngleInterface,
	App\Model\ShotInterface,
	App\Model\User,
	App\Model\Howitzer,
	App\Model\Target,
	App\Model\Distance,
	App\Model\Speed,
	App\Model\Angle,
	App\Model\Shot;
	
class ShotMapper extends AbstractDataMapper implements ShotMapperInterface {
	protected $userMapper;
	protected $howitzerMapper;
	protected $targetMapper;
	protected $distanceMapper;
	protected $speedMapper;
	protected $angleMapper;
	protected $entityTable = "shot";
	
	public function __construct(DatabaseAdapterInterface $adapter, 
								UserMapperInterface $userMapper,
								HowitzerMapperInterface $howitzerMapper,
								TargetMapperInterface $targetMapper,
								DistanceMapperInterface $distanceMapper,
								SpeedMapperInterface $speedMapper,
								AngleMapperInterface $angleMapper
	){
		$this->userMapper = $userMapper;
		$this->howitzerMapper = $howitzerMapper;
		$this->targetMapper = $targetMapper;
		$this->distanceMapper = $distanceMapper;
		$this->speedMapper = $speedMapper;
		$this->angleMapper = $angleMapper;
		parent::__construct($adapter);
	}

	public function insert(	UserInterface $user, 
							HowitzerInterface $howitzer, 
							TargetInterface $target, 
							DistanceInterface $distance, 
							SpeedInterface $speed, 
							AngleInterface $angle){
		return $this->adapter->insert(
			$this->entityTable,
			array(
				'id_user' => $user->id,
				'id_howitzer' => $howitzer->id,
				'id_target' => $target->id,
				'id_distance' => $distance->id,
				'id_speed' => $speed->id,
				'id_angle' => $angle->id,
				'created_date' => date("Y-m-d H:i:s")
			)
		);
	}
	
	public function delete(ShotInterface $shot){
		$id = $shot->getId();
		return $this->adapter->delete($this->entityTable, "id = $id");
	}

	public function deleteByUser(UserInterface $user){
		$id = $user->getId();
		return $this->adapter->delete($this->entityTable, "id_user = $id");
	}
	
	protected function createEntity(array $row){
		$user = $this->userMapper->findById($row["id_user"]);
		$howitzer = $this->howitzerMapper->findById($row["id_howitzer"]);
		$target = $this->targetMapper->findById($row["id_target"]);
		$distance = $this->distanceMapper->findById($row["id_distance"]);
		$speed = $this->speedMapper->findById($row["id_speed"]);
		$angle = $this->angleMapper->findById($row["id_angle"]);
		$shot = new Shot($user, $howitzer, $target, $distance, $speed, $angle, $row['created_date']);
		$shot->setId($row['id']);
		return $shot;
	}
}