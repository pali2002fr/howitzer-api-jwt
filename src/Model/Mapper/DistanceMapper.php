<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\DistanceInterface,
	App\Model\Distance;
	
class DistanceMapper extends AbstractDataMapper implements DistanceMapperInterface {
	protected $entityTable = "distance";
	
	public function insert(DistanceInterface $distance){
		$distance->id = $this->adapter->insert(
			$this->entityTable,
			array(
				'distance' => $distance->distance,
				'created_date' => date("Y-m-d H:i:s")
			)
		);
		return $distance->id;
	}
	
	public function delete($id){
		if($id instanceOf DistanceInterface){
			$id = $id->id;
		}
		
		return $adapter->delete($this->entityTable, "id = $id");
	}
	
	protected function createEntity(array $row){
		$distance = new Distance($row['distance']);
		$distance->setId($row['id']);
		return $distance;
	}
}