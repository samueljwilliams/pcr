<?php
/**
 * 
 * A Query object.
 * @author 
 * @version 1.0
 * 
 */
class Query {
	private $pm;
	private $statement;
	private $workspace;
	
	/**
	 * 
	 * Creates a new Query object, given an active PersistenceManager and valid query statement.
	 * @param generic $pm the active PersistenceManager.
	 * @param string $statement the query statement.
	 * 
	 */
	public function __construct(&$pm, $statement) {
		$this->pm =& $pm;
		$this->statement = $statement;

		$this->workspace = $pm->getWorkspace();
	}
	
	/**
	 * 
	 * Executes this query and returns a QueryResult.
	 * @return QueryResult a query result.
	 * 
	 */
	public function execute() {
		$statement = '';
		
		foreach ($this->statement as $value)
		{
			$statement .= '{' . implode ( ',', $value ) . '}';
		}

		Log4PCR::info ( 'Executed query: ' . $this->workspace . '=>' . $statement );
		
		return new QueryResult($this->pm, $this->pm->query($this->statement));
	}
	
	/**
	 * 
	 * Returns the statement set for this query.
	 * This could be anything (string, object, airplane) - so beware.
	 * @return generic the query statement.
	 * 
	 */
	public function getStatement() {
		return $this->statement;
	}

	/**
	 * 
	 * Creates a node representing this Query in content.
	 * An ItemExistsException will be thrown immediately if an item at the specified path already exists.
	 * A PathNotFoundException will be thrown immediately if the specified path implies intermediary nodes that do not exist.
	 * @param string $path the absolute path of the node to be created.
	 * @return Node the newly created node.
	 * 
	 */
	public function storeAsNode($path) {
		$statement = '';
		
		foreach ($this->statement as $value)
		{
			$statement .= '{' . implode ( ',', $value ) . '}';
		}

		$node = new Node($this->pm, 'pcr:root');
		$node = $node->addNode(substr($path, strlen('pcr:root/')));
		$node->setProperty('pcr:statement', $statement);
		
		Log4PCR::info("Stored query as node: $this->workspace=>" . $node->getPath());
		
		return $node;
	}
}

?>