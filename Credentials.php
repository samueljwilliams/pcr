<?php
/**
 * 
 * Credentials class represents simple user ID/password credentials.
 * @author 
 * @version 1.0
 * 
 */
class Credentials {
	private $attributes = array();
	private $password;
	private $userID;
	
	/**
	 * 
	 * Creates a new Credentials object, given a user ID and password.
	 * @param string $userID the user ID.
	 * @param string $password the user's password.
	 * 
	 */
	public function __construct($userID, $password) {
		$this->userID = $userID;
		$this->password = $password;
	}
	
	/**
	 * 
	 * Returns the value of the named attribute as a string, or null if no attribute of the given name exists.
	 * @param string $name specifying the name of the attribute.
	 * @return string|null containing the value of the attribute, or null if the attribute does not exist.
	 * 
	 */
	public function getAttribute($name) {
		if (isset($this->attributes[$name])) {
			$attribute = $this->attributes[$name];
		} else {
			$attribute = null;
		}
		
		return $attribute;
	}
	
	/**
	 * 
	 * Returns the names of the attributes available to this credentials instance.
	 * This method returns an empty array if the credentials instance has no attributes available to it.
	 * @return array containing the names of the stored attributes.
	 * 
	 */
	public function getAttributeNames() {
		return array_keys($this->attributes);
	}
	
	/**
	 * 
	 * Returns the user password.
	 * @return string the password.
	 * 
	 */
	public function getPassword() {
		return $this->password;
	}
	
	/**
	 * 
	 * Returns the user ID.
	 * @return string the user ID.
	 * 
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * 
	 * Removes an attribute from this credentials instance.
	 * @param string $name specifying the name of the attribute to remove.
	 *  
	 */
	public function removeAttribute($name) {
		if (isset($this->attributes[$name])) {
			unset($this->attributes[$name]);
		}
	}
	
	/**
	 * 
	 * Stores an attribute in this credentials instance.
	 * If attribute name is empty, a RepositoryException is thrown.
	 * @param string $name specifying the name of the attribute.
	 * @param string $value to be stored.
	 * 
	 */
	public function setAttribute($name, $value) {
		$name = trim($name);
		
		if (empty($name)) {
			throw new RepositoryException("cannot set attribute with empty name");
		}
		
		$this->attributes[$name] = $value;
	}
}

?>