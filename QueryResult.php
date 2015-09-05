<?php
/**
 * 
 * The QueryResult object.
 * Allows one to get the nodes found in a query.
 * Provides methods for the creation and retrieval of search queries.
 * @author 
 * @version 1.0
 * 
 */
class QueryResult {
	private $pm;
	private $nodes;
	
	/**
	 * 
	 * Creates a new QueryResult object, given an active PersistenceManager and an array of nodes.
	 * @param generic $pm the active PersistenceManager.
	 * 
	 */
	public function __construct(&$pm, $nodes) {
		$this->pm =& $pm;
		$this->nodes = $nodes;
	}
	
	/**
	 * 
	 * Returns an iterator over all nodes that match the query.
	 * If no items match, an empty iterator is returned.
	 * @return NodeIterator the nodes returned from the query.
	 * 
	 */
	public function getNodes() {
		return new NodeIterator($this->pm, $this->nodes);
	}
}

?>