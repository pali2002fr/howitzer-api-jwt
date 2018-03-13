<?php
namespace App\Model;

class Howitzer extends AbstractEntity implements HowitzerInterface
{
    protected $_id;
    protected $_weight;

    public function __construct($weight) {
        $this->setWeight($weight);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID for this howitzer has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The howitzer ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setWeight($weight) {
        if (!is_numeric($weight) || ($weight < 1)) {
            throw new \InvalidArgumentException("The howitzer weight is invalid.");
        }
 
        $this->_weight = $weight;
        return $this;
    }

    public function getWeight() {
        return $this->_weight;
    }
}