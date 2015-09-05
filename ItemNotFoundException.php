<?php
/**
 * 
 * Exception thrown by methods of Item, Node and Workspace when an item is not found.
 * @author 
 * @version 1.0
 * 
 */
class ItemNotFoundException extends Exception {
	public function __construct($message = null) {
		$message = "Item not found exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
