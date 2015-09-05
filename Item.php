<?php
/**
 * 
 * The Item class is the base class of Node and Property.
 * @author 
 * @version 1.0
 * 
 */
class Item {
	protected $name;
	protected $path;
	protected $pm;
	protected $value;
	protected $workspace;
	
	/**
	 * 
	 * Creates a new Item object, given an active Persistence Manager, path, name <optional>, and value <optional>.
	 * If the name and value have not been included, one can assume the item is a node. 
	 * @param PersistenceManager $pm the currently active PM.
	 * @param string $path the path of the item.
	 * @param string $name the name of the item <optional>.
	 * @param string $value the value of the item <optional>.
	 * 
	 */
	public function __construct(&$pm, $path, $name = null, $value = null) {
		$this->pm =& $pm;
		$this->path = $path;
		$this->name = $name;
		$this->value = $value;
		
		$this->workspace = $pm->getWorkspace();
	}
	
	/**
	 * 
	 * Returns the ancestor Node of the specified depth.
	 * An ancestor of depth x is the Node that is x levels down along the path from the root node to this Item.
	 * $depth = 0 returns the root node.
	 * $depth = 1 returns the child of the root node along the path to this Item.
	 * $depth = 2 returns the grandchild of the root node along the path to this Item.
	 * And so on to depth = n, where n is the depth of this Item, which returns this Item itself.
	 * If depth > n is specified then a ItemNotFoundException is thrown.
	 * @param integer $depth an integer, 0 <= depth <= n where n is the depth of this Item.
	 * @return Node the ancestor of this Item at the specified depth.
	 * 
	 */
	public function getAncestor($depth) {
		if ($depth < 0 || $depth >= count(explode('/', $this->getPath()))) {
			throw new ItemNotFoundException("ancestor ($depth) of item: $this->workspace=>" . $this->getPath());
		}

		$path = implode('/', array_slice(explode('/', $this->getPath()), 0, $depth + 1));
		
		Log4PCR::info("Requested node: $this->workspace=>$path");
		
		return new Node($this->pm, $path);
	}
	
	/**
	 * 
	 * Returns the depth of this Item in the workspace tree.
	 * Returns the depth below the root node of this Item (counting this Item itself).
	 * The root node returns 0.
	 * A property or child node of the root node returns 1.
	 * A property or child node of a child node of the root returns 2.
	 * And so on to this Item.
	 * @return integer the depth of this Item in the workspace hierarchy.
	 * 
	 */
	public function getDepth() {
		return count(explode('/', $this->getPath())) - 1;
	}
	
	/**
	 * 
	 * Returns the name of this Item.
	 * The name of an item is the last element in its path.
	 * If this Item is the root node of the workspace (i.e., if $this->getDepth() == 0), 'pcr:root' will be returned.
	 * @return string the (or a) name of this Item or 'pcr:root' if this Item is the root node.
	 * 
	 */
	public function getName() {
		if ($this->name !== null) {
			$name = $this->name;
		} else if ($this->path == 'pcr:root') {
			$name = 'pcr:root';
		} else {
			$name = substr($this->path, strrpos($this->path, '/') + 1);
		}
			
		return $name;
	}
	
	/**
	 * 
	 * Returns the parent Node of this Item.
	 * If at the root Node, an ItemNotFoundException is thrown (i.e. there is no parent).
	 * @return Node the parent Node of this Item.
	 * 
	 */
	public function getParent() {
		if ($this->path == 'pcr:root') {
			throw new ItemNotFoundException("parent of item: $this->workspace=>pcr:root");
		} else if ($this->isNode()) {
			$path = substr($this->path, 0, strrpos($this->path, '/'));
		} else {
			$path = $this->path;
		}
			
		Log4PCR::info("Requested node: $this->workspace=>$path");
			
		return new Node($this->pm, $path);
	}
	
	/**
	 * 
	 * Returns the absolute path to this item.
	 * @return string the path of this Item.
	 * 
	 */
	public function getPath() {
		if ($this->isNode()) {
			$path = $this->path;
		} else {
			$path = "$this->path/$this->name";
		}
		
		return $path;
	}
	
	/**
	 * 
	 * Indicates whether this Item is a Node or a Property.
	 * Returns true if this Item is a Node; Returns false if this Item is a Property.
	 * @return boolean true if this Item is a Node, false if it is a Property.
	 * 
	 */
	public function isNode() {
		if ($this->name === null && $this->value === null) {
			$isNode = true;
		} else {
			$isNode = false;
		}
				
		return $isNode;
	}

	/**
	 * 
	 * Returns true if this Item object represents the same actual repository item as the object $item.
	 * @return boolean true if this Item object and $item represent the same actual repository item; false otherwise.
	 * 
	 */
	public function isSame(Item $item) {
		if ($this->path == $item->getPath() && $this->name == $item->getName()) {
			$isSame = true;
		} else {
			$isSame = false;
		}
		
		return $isSame;
	}
	
	/**
	 * 
	 * Removes this item (and its subtree).
	 * A LockException will be thrown immediately if a lock prevents the removal of this item.
	 * 
	 */
	public function remove() {
		if ($this->isNode()) {
			if ($this->path == 'pcr:root') {
				throw new RepositoryException("cannot remove reserved node: $this->workspace=>$this->path");
			} else if ($this->pm->getProperty($this->path, 'pcr:isLocked')) {
				throw new LockException("cannot remove locked node: $this->workspace=>$this->path");
			} else if ($this->pm->hasReferences($this->path)) {
				throw new ReferentialIntegrityException("cannot remove referenced node: $this->workspace=>$this->path");
			}
			
			$this->pm->removeNode($this->path);
			
			Log4PCR::info("Removed node: $this->workspace=>$this->path");
		} else {
			if (substr($this->name, 0, 4) == 'pcr:') {
				throw new RepositoryException("cannot remove reserved property: $this->workspace=>$this->path/$this->name");
			} else if ($this->pm->getProperty($this->path, 'pcr:isLocked')) {
				throw new LockException("cannot remove property from locked node: $this->workspace=>$this->path");
			}

			$this->pm->removeProperty($this->path, $this->name);
			
			$this->pm->setProperty($this->path, 'pcr:lastModified', Utility::getTimeStamp());
			
			Log4PCR::info("Removed property: $this->workspace=>$this->path/$this->name");
		}
	}

	/**
	 * 
	 * Casts Item object as a Node and returns.
	 * @return Node the Item casted as a Node.
	 * 
	 */
	public function toNode() {
		return new Node($this->pm, $this->path);
	}

	/**
	 * 
	 * Casts Item object as a Property and returns.
	 * @return Property the Item casted as a Property.
	 * 
	 */
	public function toProperty() {
		return new Property($this->pm, $this->path, $this->name, $this->value);
	}
}

?>