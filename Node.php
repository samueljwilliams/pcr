<?php
/**
 * 
 * A Node class represents a Node object in the hierarchy that makes up the content repository.
 * A node must have one and only one parent node.
 * A node can have children.
 * The Node class extends the Item class.
 * @author 
 * @version 1.0
 * 
 */
class Node extends Item {
	/**
	 * 
	 * Creates a new node at $path.
	 * The new node will be persisted immediately.
	 * Strictly speaking, the parameter is actually a relative path to the parent node of the node to be added, appended with the name desired for the new node (if the a node is being added directly below this node then only the name need be specified).
	 * An ItemExistsException will be thrown immediately if an item at the specified path already exists.
	 * A PathNotFoundException will be thrown immediately if the specified path implies intermediary nodes that do not exist.
	 * @param string $path the path of the new node to be created.
	 * @return Node the node that was added.
	 * 
	 */
	public function addNode($path) {
		$fullPath = "$this->path/$path";
		$lastSlash = strrpos($fullPath, '/');
		
		$name = trim(substr($fullPath, $lastSlash + 1));

		if (empty($name)) {
			throw new RepositoryException("cannot add node with empty name");
		}
				
		$parent = substr($fullPath, 0, $lastSlash);
		
		if ($parent != 'pcr:root') {
			if ($this->pm->getNode($parent) === null) {
				throw new PathNotFoundException("$this->workspace=>$parent");
			}
		} else if ($this->pm->getNode($fullPath) !== null) {
			throw new ItemExistsException("$this->workspace=>$fullPath");
		}
							
		$this->pm->addNode($fullPath);
		$this->pm->setProperty($fullPath, 'pcr:created', Utility::getTimeStamp());
		$this->pm->setProperty($fullPath, 'pcr:isLocked', false);
		
		Log4PCR::info("Added node: $this->workspace=>$fullPath");		
		
		return new Node($this->pm, $fullPath);
	}
	
	/**
	 * 
	 * Returns the node at $path relative to this node.
	 * A PathNotFoundException will be thrown immediately if no node exists at the specified path.
	 * @param string $path the relative path of the node to retrieve.
	 * @return Node the node at $path.
	 * 
	 */
	public function getNode($path) {
		$fullPath = "$this->path/$path";
		
		if ($this->pm->getNode($fullPath) === null) {
			throw new PathNotFoundException("$this->workspace=>$fullPath");
		}
					
		Log4PCR::info("Requested node: $this->workspace=>$fullPath");
		
		return new Node($this->pm, $fullPath);
	}

	/**
	 * 
	 * Returns a NodeIterator over all child Nodes of this Node.
	 * Does not include properties of this Node.
	 * The same reacquisition semantics apply as with getNode(String).
	 * If this node has no child nodes, then an empty iterator is returned.
	 * @return NodeIterator all child Nodes of this Node.
	 * 
	 */
	public function getNodes() {
		return new NodeIterator($this->pm, $this->pm->getNodes($this->path));
	}
	
	/**
	 * 
	 * Returns all properties of this node.
	 * Returns a PropertyIterator over all properties of this node.
	 * Does not include child nodes of this node.
	 * The same reacquisition semantics apply as with getProperty(String).
	 * If this node has no properties, then an empty iterator is returned.
	 * @return PropertyIterator all Properties of this Node.
	 * 
	 */
	public function getProperties() {
		return new PropertyIterator($this->pm, $this->pm->getProperties($this->path));
	}
	
	/**
	 * 
	 * Returns the property at $path relative to this node.
	 * The same reacquisition semantics apply as with getNode(String).
	 * @param string $path the relative path of the property to retrieve.
	 * @return Property the property at $path.
	 * 
	 */
	public function getProperty($path) {
		$fullPath = "$this->path/$path";
		$lastSlash = strrpos($fullPath, '/');
		
		$parent = substr($fullPath, 0, $lastSlash);
		$property = substr($fullPath, $lastSlash + 1);
		
		$result = $this->pm->getProperty($parent, $property);
		
		if ($result === null) {
			throw new PathNotFoundException("$this->workspace=>$fullPath");
		}
					
		Log4PCR::info("Requested property: $this->workspace=>$fullPath");			
		
		return new Property($this->pm, $parent, $property, $result);
	}

	/**
	 * 
	 * Returns all reference properties that refer to this node.
	 * Returns a PropertyIterator over all properties that refer to this node.
	 * If this node is not referenced, then an empty iterator is returned.
	 * @return PropertyIterator all reference properties that refer to this Node.
	 * 
	 */
	public function getReferences() {
		return new PropertyIterator($this->pm, $this->pm->getReferences($this->path));
	}
	
	/**
	 * 
	 * Indicates whether a node exists at $path.
	 * Returns true if a node exists at $path and false otherwise.
	 * @param string $path the path of a (possible) node.
	 * @return boolean true if a node exists at $path; false otherwise.
	 * 
	 */
	public function hasNode($path) {
		if ($this->pm->getNode("$this->path/$path") !== null) {
			$exists = true;
		} else {
			$exists = false;
		}
		
		return $exists;
	}

	/**
	 * 
	 * Indicates whether this node has child nodes.
	 * Returns true if this node has one or more child nodes; false otherwise.
	 * @return boolean true if this node has one or more child nodes; false otherwise.
	 * 
	 */
	public function hasNodes() {
		return $this->pm->hasNodes($this->path);
	}

	/**
	 * 
	 * Indicates whether this node has properties.
	 * Returns true if this node has one or more properties; false otherwise.
	 * @return boolean true if this node has one or more properties; false otherwise.
	 * 
	 */
	public function hasProperties() {
		return $this->pm->hasProperties($this->path);
	}

	/**
	 * 
	 * Indicates whether a property exists at $path.
	 * Returns true if a property exists at $path and false otherwise.
	 * @param string $path the path of a (possible) property.
	 * @return boolean true if a property exists at $path; false otherwise.
	 * 
	 */
	public function hasProperty($path) {
		$fullPath = "$this->path/$path";
		$lastSlash = strrpos($fullPath, '/');
		
		$parent = substr($fullPath, 0, $lastSlash);
		$property = substr($fullPath, $lastSlash + 1);
		
		if ($this->pm->getProperty($parent, $property) !== null) {
			$hasProperty = true;
		} else {
			$hasProperty = false;
		}
				
		return $hasProperty;
	}

	/**
	 * 
	 * Indicates whether this node has been referenced.
	 * Returns true if this node has been referenced; false otherwise.
	 * @return boolean true if this node has been referenced; false otherwise.
	 * 
	 */
	public function hasReferences() {
		return $this->pm->hasReferences($this->path);
	}
	
	/**
	 * 
	 * Returns true if this node holds a lock; otherwise returns false.
	 * To hold a lock means that this node has actually had a lock placed on it specifically.
	 * 
	 */
	public function isLocked() {
		if ($this->pm->getProperty($this->path, 'pcr:isLocked')) {
			$isLocked = true;
		} else {
			$isLocked = false;
		}
				
		return $isLocked;
	}
	
	/**
	 * 
	 * Places a lock on this node.
	 * If successful, this node is said to hold the lock.
	 * If this node is already locked, a LockException is thrown.
	 * 
	 */
	public function lock() {
		if ($this->isLocked()) {
			throw new LockException("cannot lock already locked node: $this->workspace=>$this->path");
		}
				
		$this->pm->setProperty($this->path, 'pcr:isLocked', true);
		$this->pm->setProperty($this->path, 'pcr:lastModified', Utility::getTimeStamp());
	}
	
	/**
	 * 
	 * Sets the specified property to the specified value.
	 * If the property does not yet exist, it is created.
	 * A best-effort data type conversion/persistence is attempted.
	 * Passing a null as the second parameter removes the property.
	 * It is equivalent to calling remove on the Property object itself.
	 * For example, N.setProperty("P", null) would remove property called "P" of the node in N.
	 * A LockException will be thrown immediately if a lock prevents the setting of the property.
	 * @param string $name the name of a property of this node.
	 * @param generic $value the value to assigned.
	 * @return Property the updated Property object.
	 * 
	 */
	public function setProperty($name, $value) {
		if ($this->path == 'pcr:root') {
			throw new RepositoryException("cannot set property to reserved node: $this->workspace=>pcr:root");
		} else if (substr($name, 0, 4) == 'pcr:') {
			throw new RepositoryException("cannot set reserved property: $this->workspace=>$this->path/$name");
		} else if ($this->isLocked()) {
			throw new LockException("cannot set property to locked node: $this->workspace=>$this->path");
		}
					
		$name = trim($name);
			
		if (empty($name)) {
			throw new RepositoryException("cannot set property with empty name");
		} else if ($value === null) {
			$this->pm->removeProperty($this->path, $name);
			
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
			
			$this->pm->setProperty($this->path, $name, $value);

			Log4PCR::info("Set property: $this->workspace=>$this->path/$name");
		}
		
		$this->pm->setProperty($this->path, 'pcr:lastModified', Utility::getTimeStamp());
		
		return new Property($this->pm, $this->path, $name, $value);
	}

	/**
	 * 
	 * Removes the lock on this node.
	 * These changes are persisted automatically.
	 * If this node does not currently hold a lock, then a LockException is thrown.
	 * 
	 */
	public function unlock() {
		if (!$this->isLocked()) {
			throw new LockException("cannot unlock already unlocked node: $this->workspace=>$this->path");
		}
				
		$this->pm->setProperty($this->path, 'pcr:isLocked', false);
		$this->pm->setProperty($this->path, 'pcr:lastModified', Utility::getTimeStamp());
	}
}

?>