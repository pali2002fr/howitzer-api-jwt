<?php
namespace App\Model\Mapper;
use App\Library\Database\DatabaseAdapterInterface;
abstract class AbstractDataMapper {
	protected $adapter;
	protected $entityTable;
	
	public function __construct(DatabaseAdapterInterface $adapter){
		$this->adapter = $adapter;
	}
	
	public function getAdapter(){
		return $this->adapter;
	}
	
	public function findById($id){
		$this->adapter->select(
			$this->entityTable, 
			array('id' => array(
					'value' => $id,
					'operator' => '='
				)
			)
		);
		if(!$row = $this->adapter->fetch()){
			return null;
		}
		
		return $this->createEntity($row);
	}
	
	public function findAll(array $conditions = array(), $orderBy = ""){
		$entities = array();
		$this->adapter->select($this->entityTable, $conditions, $orderBy);
		$rows = $this->adapter->fetchAll();
		
		if($rows){
			foreach($rows as $row){
				$entities[] = $this->createEntity($row);
			}
		}
		return $entities;
	}
	
	abstract protected function createEntity(array $row);
}