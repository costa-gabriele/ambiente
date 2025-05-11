<?php namespace _\Files;

class FileData {
	
	protected
		$name,
		$path,
		$size,
		$type,
		$error
	;
	
	public function __construct(array $pFileData) {
		
		$this->name = $pFileData['name'] ?? '';
		$this->path = $pFileData['path'] ?? '';
		$this->size = $pFileData['size'] ?? null;
		$this->type = $pFileData['type'] ?? '';
		$this->error = $pFileData['error'] ?? null;

	}

	# Getters and setters
	
	public function getName(): string {
		return $this->name;
	}
	
	public function getPath(): string {
		return $this->path;
	}
	
	public function getSize(): int {
		return $this->size;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function getError(): ?string {
		return $this->error;
	}

	public function setName(string $pName): bool {
		$this->name = $pName;
		return true;
	}

	public function setPath(string $pPath): bool {
		$this->path = $pPath;
		return true;
	}

	public function setSize(int $pSize): bool {
		$this->size = $pSize;
		return true;
	}

	public function setType(string $pType): bool {
		$this->type = $pType;
		return true;
	}

	public function setError(string $pError): bool {
		$this->error = $pError;
		return true;
	}

	# / Getters and setters
	
}

?>
