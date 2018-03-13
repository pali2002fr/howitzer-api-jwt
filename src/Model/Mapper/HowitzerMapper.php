<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface,
	App\Model\HowitzerInterface,
	App\Model\Howitzer;
	
class HowitzerMapper extends AbstractDataMapper implements HowitzerMapperInterface {
	protected $entityTable = "howitzer";
	
	public function insert(HowitzerInterface $howitzer){
		$howitzer->id = $this->adapter->insert(
			$this->entityTable,
			array(
				'weight' => $howitzer->weight,
				'created_date' => date("Y-m-d H:i:s")
			)
		);
		return $howitzer->id;
	}
	
	public function delete($id){
		if($id instanceOf HowitzerInterface){
			$id = $id->id;
		}
		
		return $adapter->delete($this->entityTable, "id = $id");
	}
	
	protected function createEntity(array $row){
		$howitzer = new Howitzer($row['weight']);
		$howitzer->setId($row['id']);
		return $howitzer;
	}
}