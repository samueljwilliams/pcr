<?php
/**
 * 
 * Exception thrown when no Item exists at the specified path or when the specified path implies intermediary Nodes that do not exist.
 * @author 
 * @version 1.0
 * 
 */
class PathNotFoundException extends Exception {
	public function __construct($message = null) {
		$message = "Path not found exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
