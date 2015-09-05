<?php
/**
 * 
 * Exception thrown on referential integrity violation.
 * @author 
 * @version 1.0
 * 
 */
class ReferentialIntegrityException extends Exception {
	public function __construct($message = null) {
		$message = "Referential integrity exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
