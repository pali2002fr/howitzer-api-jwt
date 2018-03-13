<?php
namespace App\Model;

interface HowitzerInterface
{
 	public function setId($id);
 	public function getId();

 	public function setWeight($weight);
 	public function getWeight();
}