<?php namespace _\Files;

class FileLoader {
	
	protected
		$status,
		$fileTmpData,
		$fileData,
		$fileDir = FILES_DIR,
		$fileName,
		$error
	;
	
	# The constructor uses the standard php file array
	public function __construct(array $pFileTmpData) {
		
		$this->status = 0;
		$this->fileTmpData = $pFileTmpData;

		$this->fileData = new FileData (
			[
				'name' => $this->fileTmpData['name'] ?? null,
				'path' => $this->fileTmpData['tmp_name'] ?? null,
				'size' => $this->fileTmpData['size'] ?? null,
				'type' => $this->fileTmpData['type'] ?? null,
				'error' => $this->fileTmpData['error'] ?? null
			]
		);

		if(!empty($this->fileData->getName()))
			$this->status = 1; # There's a file

		if(!empty($this->fileData->getPath())) { 
			$date = new \DateTimeImmutable();
			$this->fileName = $date->format('YmdHis') . '_' . $pFileTmpData['name'];
			$this->status = 2; # The file was moved to the temporary directory
		}

	}

	public function save(): bool {

		if(empty($this->fileTmpData) || $this->status <> 2)
			return false;

		try {

			$filePath = realpath($this->fileDir) . DIR_SEP . $this->fileName;
			$fValid = move_uploaded_file($this->fileTmpData['tmp_name'], $filePath);
		
			if($fValid) {
				$this->fileData->setPath($filePath);
				$this->status = 3; # File saved
				return true;
			} else
				return false;
					
		} catch(\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
		
	}
	
	# Getters and setters
	
	public function getStatus(): int {
		return $this->status;
	}
	
	public function getFileDir(): string {
		return $this->fileDir;
	}

	public function getFileName(): string {
		return $this->fileName;
	}

	public function getFilePath(): string|bool {
		return realpath($this->fileDir . DIR_SEP . $this->fileName);
	}

	public function getFileData(): ?FileData {
		return $this->fileData;
	}
	
	public function getError(): ?string {
		return $this->error;
	}

	public function setFileDir(string $pFileDir): bool {
		$this->fileDir = $pFileDir;
		return true;
	}

	public function setFileName(string $pFileName): bool {
		$this->fileName = $pFileName;
		return true;
	}

	# / Getters and setters
	
}

?>
