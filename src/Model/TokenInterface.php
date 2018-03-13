<?php
namespace App\Model;

interface TokenInterface
{
 	public function setId($id);
 	public function getId();

 	public function setToken($token);
 	public function getToken();
 	
 	public function setUser( UserInterface $user);
 	public function getUser();
 	
 	public function setCreated_date($created_date);
 	public function getCreated_date();
 	
 	public function setExpiration_date($expiration_date);
 	public function getExpiration_date();
}