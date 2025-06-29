<?php

spl_autoload_register (
	
	function ($pClassName) {
		
		$fIncluded = true;
		
		$file = CLASSES_DIR . str_replace('\\', DIR_SEP, $pClassName) . '.php';
		if(file_exists($file)) {
			require $file;
		}
		
	}
	
);

?>
