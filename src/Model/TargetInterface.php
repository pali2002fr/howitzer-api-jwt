<?php
namespace App\Model;

interface TargetInterface
{
 	public function setId($id);
 	public function getId();

 	public function setSize($size);
 	public function getSize();
}