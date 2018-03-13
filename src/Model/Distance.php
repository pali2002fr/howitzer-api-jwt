<?php
namespace App\Model;

class Distance extends AbstractEntity implements DistanceInterface
{
    protected $_id;
    protected $_distance;

    public function __construct($distance) {
        $this->setDistance($distance);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID for this distance has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The distance ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setDistance($distance) {
        if (!is_numeric($distance) || $distance < 1) {
            throw new \InvalidArgumentException("The distance value is invalid.");
        }
 
        $this->_distance = $distance;
        return $this;
    }

    public function getDistance() {
        return $this->_distance;
    }
}