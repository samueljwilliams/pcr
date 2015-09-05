<?php

class Log4PCR {
	public static function access($message) {
		$message = Utility::getTimeStamp() . " *ACCESS* $message\n";
		
		file_put_contents($_SERVER['PCR'] . '/logs/access.log', $message, FILE_APPEND);
	}
	
	public static function debug($message) {
		$message = Utility::getTimeStamp() . " *DEBUG* $message\n";
		
		file_put_contents($_SERVER['PCR'] . '/logs/debug.log', $message, FILE_APPEND);
	}
	
	public static function error($message) {
		$message = Utility::getTimeStamp() . " *ERROR* $message\n";
		
		file_put_contents($_SERVER['PCR'] . '/logs/error.log', $message, FILE_APPEND);
	}
	
	public static function info($message) {
		$message = Utility::getTimeStamp() . " *INFO* $message\n";
		
		file_put_contents($_SERVER['PCR'] . '/logs/info.log', $message, FILE_APPEND);
	}
}

?>