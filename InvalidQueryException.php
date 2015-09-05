<?php
/**
 * 
 * Exception thrown when a query is deemded invalid.
 * Usually thrown by the PM since queries can be unique to each.
 * @author 
 * @version 1.0
 * 
 */
class InvalidQueryException extends Exception {
	public function __construct($message = null) {
		$message = "Invalid query exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
