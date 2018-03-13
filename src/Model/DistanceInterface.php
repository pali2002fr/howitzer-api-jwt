<?php
namespace App\Model;

interface DistanceInterface
{
 	public function setId($id);
 	public function getId();

 	public function setDistance($distance);
 	public function getDistance();
}