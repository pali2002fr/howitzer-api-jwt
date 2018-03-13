<?php
namespace App\Model;

class Speed extends AbstractEntity implements SpeedInterface
{
    protected $_id;
    protected $_speed;

    public function __construct($speed) {
        $this->setSpeed($speed);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID matching this speed has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The speed ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setSpeed($speed) {
    	if (!is_numeric($speed) || $speed < 1) {
            throw new \InvalidArgumentException("The speed value is invalid.");
        }
 
        $this->_speed = $speed;
        return $this;
    }

    public function getSpeed() {
        return $this->_speed;
    }
}