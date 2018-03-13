<?php
namespace App\Model;
use App\Model\UserInterface;

class User extends AbstractEntity implements UserInterface
{
    protected $_id;
    protected $_firstname;
    protected $_lastname;
    protected $_username;
    protected $_password;

    public function __construct($firstname, $lastname, $username, $password) {
        $this->setFirstname($firstname);
        $this->setLastname($lastname);
        $this->setUsername($username);
        $this->setPassword($password);
    }
    
    public function setId($id) {
        if ($this->_id !== null) {
            throw new \BadMethodCallException(
                "The ID for this user has been set already.");
        }
 
        if (!is_numeric($id) || $id < 1) {
            throw new \InvalidArgumentException("The user ID is invalid.");
        }
 
        $this->_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setFirstname($firstname) {
        if (strlen($firstname) < 2 || strlen($firstname) > 30) {
            throw new \InvalidArgumentException("The user firstname is invalid.");
        }
 
        $this->_firstname = htmlspecialchars(trim($firstname), ENT_QUOTES);
        return $this;
    }

    public function getFirstname() {
        return $this->_firstname;
    }

    public function setLastname($lastname) {
        if (strlen($lastname) < 2 || strlen($lastname) > 30) {
            throw new \InvalidArgumentException("The user lastname is invalid.");
        }
 
        $this->_lastname = htmlspecialchars(trim($lastname), ENT_QUOTES);
        return $this;
    }

    public function getLastname() {
        return $this->_lastname;
    }

    public function setUsername($username) {
        if (strlen($username) < 2 || strlen($username) > 25) {
            throw new \InvalidArgumentException("The user username is invalid.");
        }
 
        $this->_username = htmlspecialchars(trim($username), ENT_QUOTES);
        return $this;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function setPassword($password) {
/*
        if (strlen($password) < 5 || strlen($password) > 8) {
            throw new \InvalidArgumentException("The user password is invalid.");
        }
*/
        $this->_password =  $password;
        return $this;
    }

    public function getPassword() {
        return $this->_password;
    }
}