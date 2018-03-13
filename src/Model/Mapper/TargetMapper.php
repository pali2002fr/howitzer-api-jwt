<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\TargetInterface,
	App\Model\Target;
	
class TargetMapper extends AbstractDataMapper implements TargetMapperInterface {
	protected $entityTable = "target";
	
	public function insert(TargetInterface $target){
		$target->id = $this->adapter->insert(
			$this->entityTable,
			array(
				'size' => $target->size,
				'created_date' => date("Y-m-d H:i:s")
			)
		);
		return $target->id;
	}
	
	public function delete($id){
		if($id instanceOf TargetInterface){
			$id = $id->id;
		}
		
		return $adapter->delete($this->entityTable, "id = $id");
	}
	
	protected function createEntity(array $row){
		$target = new Target($row['size']);
		$target->setId($row['id']);
		return $target;
	}
}