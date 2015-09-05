<?php
/**
 * 
 * The Session object provides read and write access to the content of a particular workspace in the repository.
 * The Session object is returned by Repository->login(Credentials, string).
 * It encapsulates both the authorization settings of a particular user (as specified by the passed Credentials) and a binding to the workspace specified by the workspace name passed on login.
 * Each Session object is associated one-to-one with a Workspace object.
 * The Workspace object represents a "view" of an actual repository workspace entity as seen through the authorization settings of its associated Session.
 * Session class represents the entry point into the content repository.
 * @author 
 * @version 1.0
 * 
 */
class Session {
	private $credentials;
	private $pm;
	private $workspace;
	
	/**
	 * 
	 * Creates a new Session object, given a particular user (as specified by the passed Credentials) and named workspace.
	 * @param Credentials $credentials the credentials of the user.
	 * @param string $workspace the name of the workspace.
	 * 
	 */
	public function __construct(&$pm) {
		$this->pm =& $pm;
		
		$this->credentials = $pm->getCredentials();
		$this->workspace = $pm->getWorkspace();
	}
	
	public function __destruct() {
	}
	
	private function buildXML(Node $node, $xml) {
		$xml .= '<node name="' . htmlentities($node->getName(), ENT_QUOTES, 'UTF-8') . '">';
		
		if ($node->hasProperties()) {
			$xml = $this->buildXMLProperties($node, $xml);
		}
		
		if ($node->hasNodes()) {
			$ni = $node->getNodes();
		
			while ($ni->hasNext()) {
				$temporaryNode = $ni->nextNode();
				
				if ($temporaryNode->hasNodes()) {
					$xml = $this->buildXML($temporaryNode, $xml);
				} else {
					$xml .= '<node name="' . htmlentities($temporaryNode->getName(), ENT_QUOTES, 'UTF-8') . '">';

					if ($temporaryNode->hasProperties()) {
						$xml = $this->buildXMLProperties($temporaryNode, $xml);
					}
					
					$xml .= '</node>';
				}
			}
		}

		return $xml .= '</node>';
	}

	private function buildXMLProperties(Node $node, $xml) {
		$pi = $node->getProperties();
		
		while ($pi->hasNext()) {
			$property = $pi->nextProperty();
			
			$xml .= '<property name="' . htmlentities($property->getName(), ENT_QUOTES, 'UTF-8') . '">';
			
			if ($property->getType() == 'array') {
				foreach ($property->getValues() as $value) {
					$xml .= '<value>' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '</value>';
				}
			} else {
				$xml .= '<value>' . htmlentities($property->getValue(), ENT_QUOTES, 'UTF-8') . '</value>';
			}
				
			$xml .= '</property>';
		}
		
		return $xml;
	}
	
	private function checkLocks(Node $node) {
		if ($node->isLocked()) {
			throw new LockException("cannot move locked node: $this->workspace=>" . $node->getPath());
		} else if ($node->hasNodes()) {
			$ni = $node->getNodes();
		
			while ($ni->hasNext()) {
				$temporaryNode = $ni->nextNode();
				
				if ($temporaryNode->hasNodes()) {
					$this->checkLocks($temporaryNode);
				} else if ($temporaryNode->isLocked()) {
					throw new LockException("cannot move locked node: $this->workspace=>" . $temporaryNode->getPath());
				}
			}
		}
	}
	
	/**
	 * 
	 * Serializes the node at $path into a series of SAX events by calling the methods of the supplied SimpleXML class.
	 * The resulting XML is in the system view form.
	 * Note that $path must be the path of a node, not a property.
	 * A PathNotFoundException is thrown if no node exists at $path.
	 * @param string $path the path of the node and it's descendants to export.
	 * @return string containing the XML of the nodes and properites found during the export.
	 * 
	 */
	public function exportXML($path) {
		if ($path != 'pcr:root') {
			if ($this->pm->getNode($path) === null) {
				throw new PathNotFoundException("$this->workspace=>$path");
			}
		}

		$xml = $this->buildXML(new Node($this->pm, $path), '<?xml version="1.0" encoding="UTF-8"?>');
		
		Log4PCR::info("Requested XML export of item: $this->workspace=>$path");
		
		return $xml;
	}
	
	/**
	 * 
	 * Returns the value of the named attribute as a string, or null if no attribute of the given name exists.
	 * @param string $name specifying the name of the attribute.
	 * @return string|null containing the value of the attribute, or null if the attribute does not exist.
	 * 
	 */
	public function getAttribute($name) {
		return $this->credentials->getAttribute($name);
	}
	
	/**
	 * 
	 * Returns the names of the attributes available to this credentials instance.
	 * This method returns an empty array if the credentials instance has no attributes available to it.
	 * @return array containing the names of the stored attributes.
	 * 
	 */
	public function getAttributeNames() {
		return $this->credentials->getAttributeNames();
	}
	
	/**
	 * 
	 * Returns the item at the specified absolute path in the workspace.
	 * @param string $path an absolute path.
	 * @return Item an item.
	 * 
	 */
	public function getItem($path) {
		if ($this->pm->getNode($path) !== null) {
			$item = new Item($this->pm, $path);
			$type = 'Node';
		} else {
			$lastSlash = strrpos($path, '/');
			
			$parent = substr($path, 0, $lastSlash);
			$property = substr($path, $lastSlash + 1);
			
			$result = $this->pm->getProperty($parent, $property);
			
			if ($result !== null) {
				$item = new Item($this->pm, $parent, $property, $result);
				$type = 'Property';
			} else {
				throw new PathNotFoundException("$this->workspace=>$path");
			}
		}
		
		Log4PCR::info("Requested item ($type): $this->workspace=>$path");
		
		return $item;
	}

	/**
	 * 
	 * Returns the root node of the workspace.
	 * The root node, "pcr:root", is the main access point to the content of the workspace.
	 * @return Node the root node of the workspace: a Node object.
	 * 
	 */
	public function getRootNode() {
		Log4PCR::info("Requested node: $this->workspace=>pcr:root");
		
		return new Node($this->pm, 'pcr:root');
	}

	/**
	 * 
	 * Returns the user ID.
	 * @return string the user ID.
	 * 
	 */
	public function getUserID() {
		return $this->credentials->getUserID();
	}

	/**
	 * 
	 * Returns the Workspace attached to this Session.
	 * @return Workspace a Workspace object.
	 * 
	 */
	public function getWorkspace() {
		return new Workspace($this->pm);
	}
	
	/**
	 * 
	 * Returns true if this Session object is usable by the client.
	 * Otherwise, returns false.
	 * A usable Session is one that is neither logged-out, timed-out nor in any other way disconnected from the repository.
	 * @return boolean true if this Session is usable, false otherwise.
	 * 
	 */
	public function isLive() {
		return $this->pm->isLive();
	}
	
	/**
	 * 
	 * Returns true if an item exists at $path; otherwise returns false.
	 * Also returns false if the specified $path is malformed.
	 * @param string $path an absolute path.
	 * @return boolean true if an item exists at $path; otherwise returns false.
	 * 
	 */
	public function itemExists($path) {
		if ($this->pm->getNode($path) !== null) {
			$itemExists = true;
		} else {
			$lastSlash = strrpos($path, '/');
			
			$parent = substr($path, 0, $lastSlash);
			$property = substr($path, $lastSlash + 1);
			
			if ($this->pm->getProperty($parent, $property) !== null) {
				$itemExists = true;
			} else {
				$itemExists = false;
			}
		}
		
		return $itemExists;
	}

	/**
	 * 
	 * Releases all resources associated with this Session.
	 * This method should be called when a Session is no longer needed.
	 * 
	 */
	public function logout() {
		$this->__destruct();
	}

	/**
	 * 
	 * Moves the node at $sourcePath (and its entire subtree) to the new location at $destinationPath.
	 * Strictly speaking, the $destinationPath parameter is actually an absolute path to the parent node of the new location, appended with the new name desired for the moved node.
	 * If no node exists at $sourcePath or no node exists one level above $destinationPath (in other words, there is no node that will serve as the parent of the moved item) then a PathNotFoundException is thrown immediately.
	 * An ItemExistsException is thrown immediately if a node already exists at $destinationPath.
	 * A LockException is thrown immediately if a node lock prevents the move.
	 * A RepositoryException is thrown if the "pcr:root" node is tried to be moved or moved to.
	 * @param string $sourcePath the root of the subtree to be moved.
	 * @param string $destinationPath the location to which the subtree is to be moved.
	 * 
	 */
	public function move($sourcePath, $destinationPath) {
		if ($sourcePath == 'pcr:root') {
			throw new RepositoryException("cannot move node: $this->workspace=>pcr:root");
		} else if ($this->pm->getNode($sourcePath) === null) {
			throw new PathNotFoundException("$this->workspace=>$sourcePath");
		} else if ($this->pm->getNode($destinationPath) !== null) {
			throw new ItemExistsException("$this->workspace=>$destinationPath");
		}

		$parent = substr($destinationPath, 0, strrpos($destinationPath, '/'));
		
		if ($parent != 'pcr:root') {
			if ($this->pm->getNode($parent) === null) {
				throw new PathNotFoundException("$this->workspace=>$parent");
			}
		}

		$this->checkLocks(new Node($this->pm, $sourcePath));
		
		Log4PCR::info("Moved node (and its descendants, if any): $this->workspace=>$sourcePath to $this->workspace=>$destinationPath");
		
		$this->pm->move($sourcePath, $destinationPath);
	}
}

?>