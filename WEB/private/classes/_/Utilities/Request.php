<?php namespace _\Utilities;

class Request implements \JsonSerializable  {

	protected
		$format,
		$uriData,
		$data,
		$files
	;
	
	public function __construct (RequestFormat $pRequestFormat = RequestFormat::JSON) {
		
		$this->format = $pRequestFormat;

		$rawData = file_get_contents('php://input');
		$multipartData = $_POST;
		$multipartFiles = $_FILES;

		switch($this->format) {

			case RequestFormat::JSON:
				$jsonData = json_decode($rawData, true);
				$requestData = $jsonData ?? $multipartData; # JSON format works also with multipart
				break;
			
			case RequestFormat::XML:
				$requestData = ['xml' => $rawData];
				break;
			
			case RequestFormat::MULTIPART:
				$requestData = $multipartData;
				break;
			
			default:
				$requestData = [];
			
		}
		
		if(isset($URI_DATA))
			$this->uriData = $URI_DATA;

		$this->setData($requestData);
		$this->setFiles($multipartFiles);

	}
	
	# JsonSerializable
	
	public function jsonSerialize(): array {
		
		$arr = [
			'data' => $this->data,
			'files' => $this->files,
		];
		
		return $arr;
		
	}
	
	# / JsonSerializable
	
	# Getters and setters
	
	public function getFormat(): RequestFormat {
		return $this->format;
	}
	
	public function getData(): array {
		return $this->data;
	}
	
	public function getFiles(): array {
		return $this->files;
	}
	
	public function getJSON(): ?string {
		return json_encode($this) ?? null;
	}
	
	public function setFormat(RequestFormat $pFormat): bool {
		$this->format = $pFormat;
		return true;
	}

	public function setData(array $pData): bool {
		$this->data = $pData;
		return true;
	}
	
	public function setFiles(array $pFiles): bool {
		$this->files = $pFiles;
		return true;
	}
	
	# / Getters and setters
	
}

?>
