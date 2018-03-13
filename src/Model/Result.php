<?php
namespace App\Model;

class Result extends AbstractEntity implements ResultInterface
{
    protected $_id;
    protected $_shot;
    protected $_user;
    protected $_hit;
    protected $_impact;

    public function __construct(ShotInterface $shot, UserInterface $user, $hit, $impact) {
        $this->setShot($shot);
        $this->setUser($user);
        $this->setHit($hit);
        $this->setImpact($impact);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID matching this result has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The result ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setShot( ShotInterface $shot) {
    	$this->_shot = $shot;
        return $this;
    }

    public function getShot() {
        return $this->_shot;
    }
    
    public function setUser( UserInterface $user) {
    	$this->_user = $user;
        return $this;
    }

    public function getHit() {
        return $this->_hit;
    }
    
    public function setHit($hit) {
    	$this->_hit = $hit;
        return $this;
    }

    public function getUser() {
        return $this->_user;
    }
    
    public function setImpact( $impact) {
    	$this->_impact = $impact;
        return $this;
    }

    public function getImpact() {
        return $this->_impact;
    }
}