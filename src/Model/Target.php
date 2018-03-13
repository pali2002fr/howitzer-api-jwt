<?php
namespace App\Model;

class Target extends AbstractEntity implements TargetInterface
{
    protected $_id;
    protected $_size;

    public function __construct($size) {
        $this->setSize($size);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID for this target has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The target ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setSize($size) {
        if (!is_numeric($size) || $size < 1) {
            throw new \InvalidArgumentException("The target size is invalid.");
        }
 
        $this->_size = $size;
        return $this;
    }

    public function getSize() {
        return $this->_size;
    }
}