<?php
namespace App\Model;

interface AngleInterface
{
 	public function setId($id);
 	public function getId();

 	public function setAngle($angle);
 	public function getAngle();
}