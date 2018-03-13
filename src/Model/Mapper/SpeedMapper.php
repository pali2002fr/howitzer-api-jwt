<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\SpeedInterface,
	App\Model\Speed;
	
class SpeedMapper extends AbstractDataMapper implements SpeedMapperInterface {
	protected $entityTable = "speed";
	
	public function insert(SpeedInterface $speed){
		$speed->id = $this->adapter->insert(
			$this->entityTable,
			array(
				'speed' => $speed->speed,
				'created_date' => date("Y-m-d H:i:s")
			)
		);
		return $speed->id;
	}
	
	public function delete($id){
		if($id instanceOf SpeedInterface){
			$id = $id->id;
		}
		
		return $adapter->delete($this->entityTable, "id = $id");
	}
	
	protected function createEntity(array $row){
		$speed = new Speed($row['speed']);
		$speed->setId($row['id']);
		return $speed;
	}
}