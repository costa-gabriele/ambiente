<?php namespace _\Utilities;

class Logger {
	
	public static function writeLog(string $pHeading, string $pEntry) {
		
		$logFileName = LOG_DIR . date('Ymd').'.log';
		$backTrace = debug_backtrace();
		$logEntry = date('H:i:s P')." - {$pHeading}\n{$pEntry}\nCALLED BY:\n{$backTrace[1]['function']}\n--------------------------------\n";
		file_put_contents($logFileName, $logEntry, FILE_APPEND);
		
	}
	
}

?>
