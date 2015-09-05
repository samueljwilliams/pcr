<?php
/**
 * 
 * This class encapsulates methods for creating a valid Query statement.
 * @author 
 * @version 1.0
 * 
 */
class QueryBuilder {
	private $statement = array();
	
	/**
	 * 
	 * Equal to (==) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function eq($name, $value) {
		$this->statement[] = array('eq', $name, $value);
		
		return $this;
	}

	/**
	 * 
	 * Greater than or equal to (>=) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function ge($name, $value) {
		$this->statement[] = array('ge', $name, $value);
		
		return $this;
	}
	
	/**
	 * 
	 * Returns the $statement of conditions.
	 * @return array an array $statement of conditions to query by.
	 *  
	 */
	public function getStatement() {
		return $this->statement;
	}
	
	/**
	 * 
	 * Greater than (>) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function gt($name, $value) {
		$this->statement[] = array('gt', $name, $value);
		
		return $this;
	}
	
	/**
	 * 
	 * Less than or equal to (<=) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function le($name, $value) {
		$this->statement[] = array('le', $name, $value);
		
		return $this;
	}
	
	/**
	 * 
	 * Like (LIKE) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function lk($name, $value) {
		$this->statement[] = array('lk', $name, $value);
		
		return $this;
	}
	
	/**
	 * 
	 * Logical operator (AND, OR, (, )) operator.
	 * @param string $operator value of operator to apply.
	 *  
	 */
	public function lo($operator) {
		$this->statement[] = array('lo', $operator);
		
		return $this;
	}
	
	/**
	 * 
	 * Less than (<) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function lt($name, $value) {
		$this->statement[] = array('lt', $name, $value);
		
		return $this;
	}

	/**
	 * 
	 * Not equal to (!=) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function ne($name, $value) {
		$this->statement[] = array('ne', $name, $value);
		
		return $this;
	}

	/**
	 * 
	 * Not like (NOT LIKE) operator.
	 * @param string $name name of property.
	 * @param string $value value of property.
	 *  
	 */
	public function nl($name, $value) {
		$this->statement[] = array('nl', $name, $value);
		
		return $this;
	}

	/**
	 * 
	 * REFERENCE operator.
	 * Used to query referenced nodes.
	 * Must be of type 'pcr:reference=>???'
	 * @param string $name name of referenced node's property.
	 * @param QueryBuilder $value value of referenced node's property.
	 *  
	 */
	public function rf($name, $value) {
		$this->statement[] = array('rf', $name, array_pop($this->statement));
		
		return $this;
	}
}

?>