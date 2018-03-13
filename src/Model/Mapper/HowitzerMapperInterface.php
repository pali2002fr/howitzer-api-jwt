<?php
namespace App\Model\Mapper;
use App\Model\HowitzerInterface;

interface HowitzerMapperInterface{
	public function findById($id);
	public function findAll(array $conditions = array());
	
	public function insert(HowitzerInterface $howitzer);
	public function delete($id);
}