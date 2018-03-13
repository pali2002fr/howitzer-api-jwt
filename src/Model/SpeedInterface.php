<?php
namespace App\Model;

interface SpeedInterface
{
 	public function setId($id);
 	public function getId();

 	public function setSpeed($speed);
 	public function getSpeed();
}