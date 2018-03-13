<?php
namespace App\Model;

class Token extends AbstractEntity implements TokenInterface
{
    protected $_id;
    protected $_token;
    protected $_user;
    protected $_created_date;
    protected $_expiration_date;

    public function __construct($token, UserInterface $user, $created_date, $expiration_date) {
        $this->setToken($token);
        $this->setUser($user);
        $this->setCreated_date($created_date);
        $this->setExpiration_date($expiration_date);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID matching this token has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("Id is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setToken($token) {
        /*
    	if (!is_string($token) || $token < 1) {
            throw new \InvalidArgumentException("The token value is invalid.");
        }
 */
        $this->_token = $token;
        return $this;
    }

    public function getToken() {
        return $this->_token;
    }

    public function setUser(UserInterface $user) { 
        $this->_user = $user;
        return $this;
    }

    public function getUser() {
        return $this->_user;
    }

    public function setCreated_date($created_date) { 
        $this->_created_date = $created_date;
        return $this;
    }

    public function getCreated_date() {
        return $this->_created_date;
    }

    public function setExpiration_date($expiration_date) { 
        $this->_expiration_date = $expiration_date;
        return $this;
    }

    public function getExpiration_date() {
        return $this->_expiration_date;
    }
}