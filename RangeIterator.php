<?php
/**
 * 
 * An iterator over a collection.
 * Includes the skip, getSize and getPosition methods.
 * The base interface of all type-specific iterators in the pcr and its subpackages.
 * @author 
 * @version 1.0
 * 
 */
class RangeIterator {
	protected $items;
	protected $pm;
	protected $position = 0;
	
	/**
	 * 
	 * Creates a new RangeIterator object, given an active PersistenceManager and items retrived when getting nodes or properties.
	 * @param PersistenceManager $pm the currently active PM.
	 * @param generic $items the items retrieved when "getting" nodes or properties.
	 * 
	 */
	public function __construct(&$pm, $items) {
		$this->pm =& $pm;
		$this->items = $items;
	}
	
	/**
	 * 
	 * Returns the number of elements in the iterator.
	 * @return Integer an integer of the size.
	 * 
	 */
	public function getSize() {
		return count($this->items);
	}

	/**
	 * 
	 * Returns true if the iteration has more elements.
	 * (In other words, returns true if next??? would return an element rather than throwing an exception.)
	 * @return Boolean true if the iterator has more elements.
	 * 
	 */
	public function hasNext() {
		if ($this->position < $this->getSize()) {
			$hasNext = true;
		} else {
			$hasNext = false;
		}
			
		return $hasNext;
	}
	
	/**
	 * 
	 * Skip a number of elements in the iterator.
	 * Moves the pointer/position ahead whatever the value is.
	 * @param integer $number the non-negative number of elements to skip.
	 * 
	 */
	public function skip($number) {
		$this->position += $number;
	}
}

?>