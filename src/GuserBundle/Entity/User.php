<?php

namespace GuserBundle\Entity;

use GuserBundle\CryptPasswordEncoder;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="GuserBundle\Repository\UserRepository") 
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="users")
 */
class User implements AdvancedUserInterface, \Serializable {
	const ALL_ROLES = ["ROLE_OPERATOR", "ROLE_ADMIN"];
	const LOST_PASSWORD_TOKEN_EXPIRATION = 3600*3;
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="username", type="string", length=255, unique=true)
	 * @Assert\Regex("/^[a-z0-9_]+$/")
	 */
	private $username;

	/**
	 * @ORM\Column(name="password", type="string", length=255)
	 */
	private $password;
	
	/**
	 * @ORM\Column(name="email", type="string", length=255, unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(name="active", type="boolean")
	 */
	private $active;

	/**
	 * @ORM\Column(name="roles", type="simple_array", nullable=true)
	 */
	private $roles;
	
	/**
	 * @ORM\Column(name="last_password_change", type="datetime", nullable=true)
	 */
	private $lastPasswordChange;
	
	
	
	/**
	 * @ORM\Column(name="lost_password_token", type="string", nullable=true)
	 */
	private $lostPasswordToken;
	/**
	 * @ORM\Column(name="lost_password_token_expiration", type="datetime", nullable=true)
	 */
	private $lostPasswordTokenExpiration;
	
	
	
	/**
	 * @ORM\Column(name="created", type="datetime")
	 */
	private $created;
	
	/**
	 * @ORM\Column(name="modified", type="datetime")
	 */
	private $modified;


	public function __construct() {
		$this->created = new \DateTime();
		$this->modified = new \DateTime();
		$this->password = "*";
		$this->active = false;
	}
	
	
	
	
	
	/* userInterface */
	public function getUsername() { return $this->username; }
	public function setUsername($username) { $this->username = $username; return $this; }

	public function getPassword() { return $this->password;	}
	public function setPassword($password, $forceValue = false) {
		if($forceValue) {
			$this->password = $password;
			$this->lastPasswordChange = new \Datetime();
		} else {
			if(!empty($password)) {
				$this->password = $this->getPasswordEncoder()->encodePassword($password, null);
				$this->lastPasswordChange = new \Datetime();
			}
		}
		return $this;
	}

	public function getRoles() { return $this->roles; }
	public function setRoles($roles) { $this->roles = $roles; return $this; }
	
	public function getSalt() {
		list($password, $salt) = $this->getPasswordEncoder()->demergePasswordAndSalt($this->password);
		return $salt;
	}
	
	public function eraseCredentials() {}



	/* AdvancedUserInterface */
	public function isEnabled() { return (bool) $this->active; }
	public function isAccountNonExpired() { return true; }
	public function isAccountNonLocked() { return true; }
	public function isCredentialsNonExpired() { return true; }	



	/** @see \Serializable::serialize() */
	public function serialize() {
		return serialize(array($this->id, $this->username, $this->active,));
	}
	/** @see \Serializable::unserialize() */
	public function unserialize($serialized) {
		list($this->id, $this->username, $this->active,) = unserialize($serialized);
	}
	


	/* other entity accessors */
	public function getId() { return $this->id; }
	
	public function getEmail() { return $this->email; }
	public function setEmail($email) { $this->email = $email; return $this; }
	
	public function getActive() { return (bool) $this->active; }
	public function setActive($active) { $this->active = $active; return $this; }
	
	public function getLastPasswordChange() { return $this->lastPasswordChange; }
	
	public function getLostPasswordToken() { return $this->lostPasswordToken; }
	public function setLostPasswordToken($lostPasswordToken) { $this->lostPasswordToken = $lostPasswordToken; return $this; }
	
	public function getLostPasswordTokenExpiration() { return $this->lostPasswordTokenExpiration; }
	public function setLostPasswordTokenExpiration($lostPasswordTokenExpiration) { $this->lostPasswordTokenExpiration = $lostPasswordTokenExpiration; return $this; }




	/* other custom functions */
		
	// used primarily by self, encoder cannot be injected from security component as it's above
	public function getPasswordEncoder() { return new CryptPasswordEncoder(); }

	static function generatePassword($length = 40) {
		$alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".CryptPasswordEncoder::SPECIAL_CHARS;
		$alphabet_max = strlen($alphabet) - 1;
		$encoder = new CryptPasswordEncoder();
		$ret = "";

		while( $encoder->isPasswordStrength("dummy", $ret) != CryptPasswordEncoder::PASSWORD_RET_OK ) {
			$ret = "";
			for ($i = 0; $i < $length; $i++) {
				$ret .= $alphabet[random_int(0, $alphabet_max)];
			}
		}
		return $ret;
	}



	/* lifecycle hooks */
	
	public function getCreated() { return $this->created; }
	public function getModified() {	return $this->modified;	}
	
	/** @ORM\PrePersist */
	public function macTimesOnPrePersist() {
		$this->created = new \DateTime();
		$this->modifies = $this->created;
    	}
	/** @ORM\PreUpdate */
	public function macTimesOnPreUpdate() {
		$this->modified = new \DateTime();
	}

}
