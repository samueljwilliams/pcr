<?php
/**
 * 
 * Base utility exception class.
 * Is thrown by many when no else is suited.
 * @author 
 * @version 1.0
 * 
 */
class RepositoryException extends Exception {
	public function __construct($message = null) {
		$message = "Repository exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
