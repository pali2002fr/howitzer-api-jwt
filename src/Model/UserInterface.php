<?php
namespace App\Model;

interface UserInterface
{
 	public function setId($id);
 	public function getId();

 	public function setFirstname($firstname);
 	public function getFirstname();

 	public function setLastname($lastname);
 	public function getLastname();

 	public function setUsername($username);
 	public function getUsername();

 	public function setPassword($password);
 	public function getPassword();
}