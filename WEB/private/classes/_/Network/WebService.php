<?php namespace _\Network;

use _\Utilities as _UT;
use _\Files as _FL;

class WebService {
	
	public const JSON_REQUEST = _UT\RequestFormat::JSON;
	public const XML_REQUEST = _UT\RequestFormat::XML;
	public const MULTIPART_REQUEST = _UT\RequestFormat::MULTIPART;
	public const JSON_RESPONSE = _UT\ResponseFormat::JSON;
	public const XML_RESPONSE = _UT\ResponseFormat::XML;
	public const HTML_RESPONSE = _UT\ResponseFormat::HTML;

	public const STATUS_CODES = [
		'200' => 'OK',
		'400' => 'Bad request',
		'404' => 'Not found',
		'500' => 'Internal server error'
	];
	
	protected
		$request,
		$response,
		$uriData = [],
		$requestFormat,
		$responseFormat,
		$statusCode,
		$headers = []
	;
	
	public function __construct(_UT\RequestFormat $pRequestFormat, _UT\ResponseFormat $pResponseFormat) {
		
		$this->requestFormat = $pRequestFormat;
		$this->responseFormat = $pResponseFormat;
		$this->request = new _UT\Request($pRequestFormat);
		$this->response = new _UT\Response($pResponseFormat);

		switch($this->responseFormat) {

			case self::JSON_RESPONSE:
				$this->addHeader('Content-Type', 'application/json');
				break;
			
			case self::HTML_RESPONSE:
				$this->addHeader('Content-Type', 'text/html');
				break;
			
			default:
				null;
			
		}

	}
	
	# Getters and setters
	
	public function getRequest(): _UT\Request {
		return $this->request;
	}

	public function getRequestData(): array {
		return $this->request->getData();
	}

	public function getRequestFiles(): array {
		return $this->request->getFiles();
	}
	
	public function getUriData(): array {
		return $this->uriData;
	}

	public function getStatus(): array {
		$status = [
			'code' => $this->statusCode,
			'message' => self::STATUS_CODES[strval($this->statusCode)]
		];
		return $status;
	}
	
	public function setStatusCode(int $pStatusCode): bool {
		$this->statusCode = $pStatusCode;
		return true;
	}
	
	public function addHeader(string $pKey, string $pValue, bool $pfMulti = false): bool {
		
		if($pfMulti && !empty($this->headers[$pKey])) { # Multiple value header
			$this->headers[$pKey][] = $pValue; 
		} else { # Single value header
			$this->headers[$pKey] = [$pValue];
		}

		return true;

	}

	public function setResponse(_UT\Response $pResponse): bool {
		$this->response = $pResponse;
		return true;
	}
	
	public function setResponseData(array|string $pResponseData): bool {
		$this->response->setData((is_array($pResponseData)) ? $pResponseData : [$pResponseData]);
		return true;
	}

	# / Getters and setters
	
	public function respond() {

		# HTTP Status code
		http_response_code($this->statusCode ?? 200);

		# Headers
		foreach($this->headers as $key => $values) {
			foreach($values as $val) {
				header($key . ': ' . $val);
			}
		}

		# Body
		$response = $this->response->getPrintable();
		echo $response;
		
	}

	public function saveFile(string $pFileKey) {

		$fileLoader = new _FL\FileLoader($this->getRequestFiles()[$pFileKey]);
		$fileLoader->save();

	}

	public function saveAllFiles() {
		foreach($this->getRequestFiles() as $file) {
			$fileLoader = new _FL\FileLoader($file);
			$fileLoader->save();
		}
	}
		
}

?>
