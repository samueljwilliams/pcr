<?php
/**
 * 
 * This interface encapsulates methods for the management of search queries.
 * Provides methods for the creation and retrieval of search queries.
 * @author 
 * @version 1.0
 * 
 */
class QueryManager {
	private $pm;
	private $workspace;
	
	/**
	 * 
	 * Creates a new QueryManager object, given an active PersistenceManager.
	 * @param generic $pm the active PersistenceManager.
	 * 
	 */
	public function __construct(&$pm) {
		$this->pm =& $pm;
		
		$this->workspace = $pm->getWorkspace();
	}
	
	/**
	 * 
	 * Creates a new query by specifying the query statement itself.
	 * It is generic enough where people can come up with their own query implemenation.
	 * If the query statement is syntactically invalid, an InvalidQueryException is thrown.
	 * @param string $statement the parameter containing the query statement.
	 * @return Query a Query object.
	 * 
	 */
	public function createQuery($statement) {
		return new Query($this->pm, $statement);
	}
	
	/**
	 * 
	 * Retrieves an existing persistent query.
	 * If node is not a valid persisted query, an InvalidQueryException is thrown.
	 * Persistent queries are created by first using QueryManager->createQuery to create a Query object and then calling Query->storeAsNode to persist the query to a location in the workspace.
	 * @param Node $node a persisted query.
	 * @return Query a Query object.
	 * 
	 */
	public function getQuery(Node $node) {
		Log4PCR::info("Requested query: $this->workspace=>" . $node->getPath());
		
		return new Query($this->pm, $node->getProperty('pcr:statement')->getString());
	}
}

?>