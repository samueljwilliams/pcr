<?php
/**
 * 
 * A Property object represents the smallest granularity of content storage.
 * A property must have one and only one parent node.
 * A property does not have children.
 * When we say that node A "has" property B it means that B is a child of A.
 * The Property class extends the Item class.
 * @author 
 * @version 1.0
 * 
 */
class Property extends Item {
	/**
	 * 
	 * Returns a boolean representation of the value of this property.
	 * @return boolean a boolean representation of the value of this property.
	 * 
	 */
	public function getBoolean() {
		if (is_array($this->value)) {
			throw new ValueFormatException("cannot get property (array) as boolean: $this->workspace=>$this->path/$this->name");
		}
				
		return (boolean) $this->value;
	}

	/**
	 * 
	 * Returns a double representation of the value of this property.
	 * @return double a double representation of the value of this property.
	 * 
	 */
	public function getDouble() {
		if (is_array($this->value)) {
			throw new ValueFormatException("cannot get property (array) as double: $this->workspace=>$this->path/$this->name");
		}
				
		return (double) $this->value;
	}
	
	/**
	 * 
	 * Returns an integer representation of the value of this property.
	 * @return integer an integer representation of the value of this property.
	 * 
	 */
	public function getInteger() {
		if (is_array($this->value)) {
			throw new ValueFormatException("cannot get property (array) as integer: $this->workspace=>$this->path/$this->name");
		}
				
		return (integer) $this->value;
	}
	
	/**
	 * 
	 * Returns the length of the value of this property.
	 * @return integer an integer.
	 * 
	 */
	public function getLength() {
		if (is_array($this->value)) {
			throw new ValueFormatException("cannot get property (array) as length: $this->workspace=>$this->path/$this->name");
		}
				
		return strlen((string) $this->value);
	}
	
	/**
	 * 
	 * Returns an array holding the lengths of the values of this (multi-value) property.
	 * Returns the number of characters needed to display the value in its string form.
	 * Returns a –1 in the appropriate position if the implementation cannot determine the length of a value.
	 * If this property is single-valued, this method throws a ValueFormatException.
	 * @return array an array of the lengths of the values in string form.
	 * 
	 */
	public function getLengths() {
		if (!is_array($this->value)) {
			throw new ValueFormatException("cannot get property (value) as lengths: $this->workspace=>$this->path/$this->name");
		}
				
		$return = array();
		
		foreach ($this->value as $value) {
			$return[] = strlen((string) $value);
		}
				
		return $return;
	}
	
	/**
	 * 
	 * Returns a Node representation of the value of this property.
	 * @return Node a Node object of the value of this property.
	 * 
	 */
	public function getNode() {
		if (!is_string($this->value)) {
			throw new ValueFormatException('cannot get property (' . gettype($this->value) . ") as node: $this->workspace=>$this->path/$this->name");
		} else if (substr($this->value, 0, strlen('pcr:reference=>')) != 'pcr:reference=>') {
			throw new ValueFormatException("cannot get property as node: $this->workspace=>$this->path/$this->name");
		}
					
		return new Node($this->pm, substr($this->value, strlen('pcr:reference=>')));
	}
	
	/**
	 * 
	 * Returns a NodeIterator representation of the value of this property.
	 * @return array a string representation of the value of this property.
	 * 
	 */
	public function getNodes() {
		if (!is_array($this->value)) {
			throw new ValueFormatException("cannot get property (value) as nodes: $this->workspace=>$this->path/$this->name");
		}
					
		$nodes = array();

		foreach ($this->value as $value) {
			if (is_string($value)) {
				if (substr($value, 0, strlen('pcr:reference=>')) == 'pcr:reference=>') {
					$nodes[] = substr($value, strlen('pcr:reference=>'));
				}
			}
		}
				
		return new NodeIterator($this->pm, $nodes);
	}
	
	/**
	 * 
	 * Returns a String representation of the value of this property.
	 * @return string a string representation of the value of this property.
	 * 
	 */
	public function getString() {
		if (is_array($this->value)) {
			throw new ValueFormatException("cannot get property (array) as string: $this->workspace=>$this->path/$this->name");
		}
				
		return (string) $this->value;
	}

	/**
	 * 
	 * Returns the type of this Property.
	 * One of: 1) string, 2) double, 3) integer, 4) boolean, 5) generic
	 * @return string the type.
	 * 
	 */
	public function getType() {
		if (is_array($this->value)) {
			$return = 'array';
		} else if (is_string($this->value)) {
			if (substr($this->value, 0, strlen('pcr:reference=>')) == 'pcr:reference=>') {
				$return = 'reference';
			} else {
				$return = 'string';
			}
		} else {
			$return = gettype($this->value);
		}
		
		return $return;
	}
	
	/**
	 * 
	 * Returns the value of this property (no casting).
	 * @return generic the value of this property.
	 * 
	 */
	public function getValue() {
		if (is_array($this->value)) {
			throw new ValueFormatException("cannot get property (array) as value: $this->workspace=>$this->path/$this->name");
		}
					
		return $this->value;
	}

	/**
	 * 
	 * Returns the values (array) of this property (no casting).
	 * @return array the values of this property.
	 * 
	 */
	public function getValues() {
		if (!is_array($this->value)) {
			throw new ValueFormatException("cannot get property (value) as values: $this->workspace=>$this->path/$this->name");
		}
				
		return (array) $this->value;
	}
	
	/**
	 * 
	 * Sets the value of this property to $value.
	 * @param generic $value the new value to set the property to.
	 * 
	 */
	public function setValue($value) {
		if (substr($this->name, 0, 4) == 'pcr:') {
			throw new RepositoryException("cannot set reserved property: $this->workspace=>$this->path/$this->name");
		} else if ($this->pm->getProperty($this->path, 'pcr:isLocked')) {
			throw new LockException("cannot set property to locked node: $this->workspace=>$this->path");
		} else if ($value === null) {
			$this->pm->removeProperty($this->path, $this->name);
			
			Log4PCR::info("Removed property: $this->workspace=>$this->path/$name");
		} else {
			if (is_object($value) && get_class($value) == 'Node') {
				$value = 'pcr:reference=>' . $value->getPath();
			} else if (is_array($value)) {
				foreach ($value as $key => $temporaryValue) {
					if (is_object($temporaryValue) && get_class($temporaryValue) == 'Node') {
						$value[$key] = 'pcr:reference=>' . $temporaryValue->getPath();
					} else if ($temporaryValue === null) {
						unset($value[$key]);
					}
				}
			}
			
			$this->pm->setProperty($this->path, $this->name, $value);

			Log4PCR::info("Set property: $this->workspace=>$this->path/$this->name");
		}
			
		$this->pm->setProperty($this->path, 'pcr:lastModified', Utility::getTimeStamp());

		$this->value = $value;
	}
}

?>