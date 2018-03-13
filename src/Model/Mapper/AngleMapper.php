<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\AngleInterface,
	App\Model\Angle;
	
class AngleMapper extends AbstractDataMapper implements AngleMapperInterface {
	protected $entityTable = "angle";
	
	public function insert(AngleInterface $angle){
		$angle->id = $this->adapter->insert(
			$this->entityTable,
			array(
				'angle' => $angle->angle,
				'created_date' => date("Y-m-d H:i:s")
			)
		);
		return $angle->id;
	}
	
	public function delete($id){
		if($id instanceOf AngleInterface){
			$id = $id->id;
		}
		
		return $adapter->delete($this->entityTable, "id = $id");
	}
	
	protected function createEntity(array $row){
		$angle = new Angle($row['angle']);
		$angle->setId($row['id']);
		return $angle;
	}
}