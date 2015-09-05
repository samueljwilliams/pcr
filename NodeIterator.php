<?php
/**
 * 
 * Allows easy iteration through a list of Nodes with nextNode as well as a skip method inherited from RangeIterator.
 * The NodeIterator class extends the RangeIterator class.
 * @author 
 * @version 1.0
 * 
 */
class NodeIterator extends RangeIterator {
	/**
	 * 
	 * Returns the next Node in the iteration.
	 * @return Node the next Node in the iteration.
	 * 
	 */
	public function nextNode() {
		$node = new Node($this->pm, $this->items[$this->position]);
		
		$this->position++;
		
		return $node;
	}
}

?>