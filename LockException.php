<?php
/**
 * 
 * Exception thrown when persisting a change (i.e. Node->setProperty(), Item->remove(), etc.) would conflict with a lock.
 * @author 
 * @version 1.0
 * 
 */
class LockException extends Exception {
	public function __construct($message = null) {
		$message = "Lock exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
