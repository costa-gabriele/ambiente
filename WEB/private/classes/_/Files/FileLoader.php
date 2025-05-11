<?php namespace _\Files;

class FileLoader {
	
	protected
		$fileTmpData,
		$fileData,
		$fileDir = UPLOADED_FILES_DIR,
		$fileName
	;
	
	# The constructor uses the standard php file array
	public function __construct(array $pFileTmpData) {
				
		if (
			(empty($pFileTmpData['name'])) ||
			(empty($pFileTmpData['tmp_name'])) ||
			(!isset($pFileTmpData['size'])) ||
			(!isset($pFileTmpData['type'])) ||
			(!isset($pFileTmpData['error']))
		) {
			return;
		}
		
		$date = new \DateTimeImmutable();
		$this->fileTmpData = $pFileTmpData;
		$this->fileName = $date->format('YmdHis') . '_' . $pFileTmpData['name'];

	}

	public function save() {

		$filePath = $this->fileDir . $this->fileName;
		$fValid = move_uploaded_file($this->fileTmpData['tmp_name'], $filePath);
		
		if(!$fValid) {
			return;
		}
		
		$this->fileData = new FileData (
			[
				'name' => $this->fileTmpData['name'],
				'path' => $filePath,
				'size' => $this->fileTmpData['size'],
				'type' => $this->fileTmpData['type'],
				'error' => $this->fileTmpData['error']
			]
		);
		
	}
	
	# Getters and setters
	
	public function getFileData(): FileData {
		return $this->fileData;
	}
	
	public function setFileDir(string $pFileDir): bool {
		$this->fileDir = $pFileDir;
	}

	# / Getters and setters
	
}

?>
