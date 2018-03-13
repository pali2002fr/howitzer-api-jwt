<?php
namespace App\Model;

class Angle extends AbstractEntity implements AngleInterface
{
    protected $_id;
    protected $_angle;

    public function __construct($angle) {
        $this->setAngle($angle);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID matching this angle has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The angle ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setAngle($angle) {
    	if (!is_numeric($angle) || $angle < 1) {
            throw new \InvalidArgumentException("The angle value is invalid.");
        }
 
        $this->_angle = $angle;
        return $this;
    }

    public function getAngle() {
        return $this->_angle;
    }
}