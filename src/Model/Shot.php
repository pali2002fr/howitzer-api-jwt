<?php

namespace App\Model;

class Shot extends AbstractEntity implements ShotInterface
{
    protected $_id;
    protected $_user;
    protected $_howitzer;
    protected $_target;
    protected $_distance;
    protected $_speed;
    protected $_angle;
    protected $_created_date;

    public function __construct( UserInterface $user, HowitzerInterface $howitzer, TargetInterface $target, DistanceInterface $distance, SpeedInterface $speed, AngleInterface $angle, $created_date) {
        // map user fields to the corresponding mutators
        $this->setUser($user);
        $this->setHowitzer($howitzer);
        $this->setTarget($target);
        $this->setDistance($distance);
        $this->setSpeed($speed);
        $this->setAngle($angle);
        $this->setCreated_date($created_date);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID for this shot has been set already.");
        }
     
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The shot ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setUser( UserInterface $user) {
        $this->_user = $user;
        return $this;
    }
    
    public function getUser() {
        return $this->_user;
    }

    public function setHowitzer( HowitzerInterface $howitzer) {     
        $this->_howitzer = $howitzer;
        return $this;
    }
    
    public function getHowitzer() {
        return $this->_howitzer;
    }
    
    public function setTarget( TargetInterface $target) {
        $this->_target = $target;
        return $this;
    }
    
    public function getTarget() {
        return $this->_target;
    }
    
    public function setDistance( DistanceInterface $distance) {
        $this->_distance = $distance;
        return $this;
    }
    
    public function getDistance() {
        return $this->_distance;
    }
    
    public function setSpeed( SpeedInterface $speed) {
        $this->_speed = $speed;
        return $this;
    }
    
    public function getSpeed() {
        return $this->_speed;
    }
    
    public function setAngle( AngleInterface $angle) {
        $this->_angle = $angle;
        return $this;
    }
    
    public function getAngle() {
        return $this->_angle;
    }

    public function setCreated_date( $created_date) {
        $this->_created_date = $created_date;
        return $this;
    }
    
    public function getCreated_date() {
        return $this->_created_date;
    }
}