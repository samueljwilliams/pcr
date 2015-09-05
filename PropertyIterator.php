<?php
/**
 * 
 * Allows easy iteration through a list of Properties with nextProperty as well as a skip method.
 * The PropertyIterator class extends the RangeIterator class.
 * @author 
 * @version 1.0
 * 
 */
class PropertyIterator extends RangeIterator {
	/**
	 * 
	 * Returns the next Property in the iteration.
	 * @return Property the next Property in the iteration.
	 * 
	 */
	public function nextProperty() {
		$property = new Property($this->pm, $this->items[$this->position][0], $this->items[$this->position][1], $this->pm->getProperty($this->items[$this->position][0], $this->items[$this->position][1]));
							
		$this->position++;
							
		return $property;
	}
}

?>