<?php namespace _\Utilities;

class Logger {
	
	protected
		$filePath,
		$fPrinting = false
	;

	public function __construct(?string $pFileName = null) {
		$fileName = $pFileName ?? date('Ymd') . '.log';
		$this->filePath = realpath(LOG_DIR) . DIR_SEP . $fileName;
	}

	public function write(string $pHeading, string $pEntry) {
		
		$backTrace = debug_backtrace();
		$logEntry =
			date('H:i:s P')." - {$pHeading}" . PHP_EOL .
			$pEntry . PHP_EOL .
			"CALLED BY: {$backTrace[1]['function']}" . PHP_EOL .
			'--------------------------------' . PHP_EOL
		;
		file_put_contents($this->filePath, $logEntry, FILE_APPEND);
		if($this->fPrinting)
			echo $logEntry;
		
	}

	# Getters and setters

	public function getFilePath(): ?string {
		return $this->filePath;
	}

	public function isPrinting() : bool {
		return $this->fPrinting;
	}

	public function setFilePath(string $pFilePath): bool {
		if(realpath($pFilePath)) {
			$this->filePath = realpath($pFilePath);
			return true;
		} else {
			return false;
		}
	}
	
	public function setPrinting(bool $pfPrinting): bool {
		$this->fPrinting = $pfPrinting;
		return true;
	}

	# / Getters and setters
	
}

?>
