<?php
/**
 * 
 * Exception thrown when a workspace has been requested but cannot be found.
 * Most likely a missing CNF.XML file.
 * @author 
 * @version 1.0
 * 
 */
class NoSuchWorkspaceException extends Exception {
	public function __construct($message = null) {
		$message = "No such workspace exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
