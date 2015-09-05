<?php
/**
 * 
 * An exception thrown when an attempt is made to place an item in a position where another item already exists.
 * @author 
 * @version 1.0
 * 
 */
class ItemExistsException extends Exception {
	public function __construct($message = null) {
		$message = "Item exists exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
