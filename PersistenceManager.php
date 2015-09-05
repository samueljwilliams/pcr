<?php

interface PersistenceManager {
	/**
	 * 
	 * This will add a node identied via the node $path.
	 * @param string $path the absolute path of the node to added.
	 * 
	 */
	public function addNode($path);

	/**
	 * 
	 * This will clone the node (and its descendants) at $workspace=>$sourcePath and copy it to the $destinationPath.
	 * @param string $workspace The name of the workspace from which the node is to be cloned.
	 * @param string $sourcePath the absolute source path of the node to cloned.
	 * @param string $destinationPath the location to which the node at $workspace=>$sourcePath is to be cloned in this workspace.
	 * 
	 */
	public function _clone($workspace, $sourcePath, $destinationPath);
	
	/**
	 * 
	 * This will copy the node (and its descendants) at $sourcePath and copy it to the $destinationPath.
	 * @param string $sourcePath the absolute source path of the node to copied.
	 * @param string $destinationPath the absolute destination path of the node to added/copied to.
	 * 
	 */
	public function copy($sourcePath, $destinationPath);

	/**
	 * 
	 * Gets the Credentials object used to connect to the current session.
	 * @return Credentials returns the credentials object used to get the PM.
	 * 
	 */
	public function getCredentials();
	
	/**
	 * 
	 * Gets the node path of the node identified via the absolute node $path.
	 * @param string $path the absolute path of the node to get.
	 * @return string|null the absolute path of the node or null if it doesn't exist.
	 * 
	 */
	public function getNode($path);

	/**
	 * 
	 * Gets the children node path(s) of the node identified via the absolute node $path.
	 * @param string $path the absolute path of the node to get the children of.
	 * @return array a list of child nodes' absolute paths if there are children, empty array otherwise.
	 * 
	 */
	public function getNodes($path);

	/**
	 * 
	 * Gets the name(s) of the properties identified via the absolute node $path.
	 * @param string $path the absolute path of the node to get the properties of.
	 * @return array a list of properties' paths and their names (i.e. array[0] = array('path', 'name'), empty array otherwise.
	 * 
	 */
	public function getProperties($path);

	/**
	 * 
	 * Gets the value of the property identified via the $path and $name params.
	 * @param string $path the absolute path of the node in which the property lives.
	 * @param string $name the name of the property to get.
	 * @return string|array|null if the node has the property, return the value(s) (could be a string or an array if the property has multiple values) or NULL if node does not have property.
	 * 
	 */
	public function getProperty($path, $name);

	/**
	 * 
	 * Gets the paths of the nodes, if any, the identified node has been referenced by.
	 * @param string $path the absolute path of the node to get the references of.
	 * @return array|null if the node has references, return the reference(s) in an array or NULL if node does not have references.
	 * 
	 */
	public function getReferences($path);
	
	/**
	 * 
	 * Gets the name of the workspace the current session is connected to.
	 * @return string|null returns the workspace if exists, otherwise null.
	 * 
	 */
	public function getWorkspace();
	
	/**
	 * 
	 * Checks to see the identified node has child nodes.
	 * @param string $path the absolute path of the node to check if children exist.
	 * @return boolean returns true if children of node exist, false otherwise.
	 * 
	 */
	public function hasNodes($path);
	
	/**
	 * 
	 * Checks to see the identified node has properties.
	 * @param string $path the absolute path of the node to check if properties exist.
	 * @return boolean returns true if properties of node exist, false otherwise.
	 * 
	 */
	public function hasProperties($path);
		
	/**
	 * 
	 * Checks to see the identified node has references.
	 * @param string $path the absolute path of the node to check if references exist.
	 * @return boolean returns true if references of node exist, false otherwise.
	 * 
	 */
	public function hasReferences($path);
	
	/**
	 * 
	 * Checks to see if the PM connection is LIVE.
	 * @return boolean returns true if link to PM exists, otherwise false.
	 * 
	 */
	public function isLive();
	
	/**
	 * 
	 * This will move the node (and its descendants) at $sourcePath and move it to the $destinationPath.
	 * @param string $sourcePath the absolute source path of the node to move.
	 * @param string $destinationPath the absolute destination path of the node to move to.
	 * 
	 */
	public function move($sourcePath, $destinationPath);

	/**
	 * 
	 * This will query the CR and return nodes in which the query was successful.
	 * @param generic $statement the statement used to query the WS.
	 * @return array a list of node absolute paths in which the query was successful.
	 * 
	 */
	public function query($statement);
	
	/**
	 * 
	 * This will remove this node and all of its descendants.
	 * @param string $path the absolute path of the node in which to remove.
	 * 
	 */
	public function removeNode($path);

	/**
	 * 
	 * This will remove this named property and its value.
	 * @param string $path the absolute path of the node in which the property lives.
	 * @param string $name the name of the property to remove.
	 * 
	 */
	public function removeProperty($path, $name);

	/**
	 * 
	 * Creates or modifies a properties value.
	 * Any existing value(s) will be over-written.
	 * Can be a string, array (non-associative), or null.
	 * If value is null, it removes the property.
	 * @param string $path the absolute path of the node in which the property lives.
	 * @param string $name the name of the property to mod.
	 * @param string|array|null $value the value(s) of the property to overwrite.
	 * 
	 */
	public function setProperty($path, $name, $value);
}

?>