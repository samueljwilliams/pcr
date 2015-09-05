<?php
/**
 * 
 * Exception thrown by Repository->login(Credentials, String) if the specified credentials are invalid for the workspace.
 * @author 
 * @version 1.0
 * 
 */
class LoginException extends Exception {
	public function __construct($message = null) {
		$message = "Login exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
