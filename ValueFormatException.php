<?php
/**
 * 
 * Thrown if an attempt is made to read the value of a property using a type-specific read method of a type into which it is not convertable.
 * @author 
 * @version 1.0
 * 
 */
class ValueFormatException extends Exception {
	public function __construct($message = null) {
		$message = "Value format exception: $message";
		
		Log4PCR::error($message);
		
		parent::__construct($message);
	}
}

?>
