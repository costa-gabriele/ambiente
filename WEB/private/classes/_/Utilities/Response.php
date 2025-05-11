<?php namespace _\Utilities;

class Response implements \JsonSerializable {

	protected
		$format,
		$code,
		$messages = [],
		$data
	;
	
	public function __construct (ResponseFormat $pResponseFormat, array $pResponse = []) {
		
		$this->format = $pResponseFormat;

		if(isset($pResponse['code'])) $this->setCode($pResponse['code']);
		if(isset($pResponse['messages'])) $this->setMessages($pResponse['messages']);
		if(isset($pResponse['data'])) $this->setData($pResponse['data']);
		
	}
	
	# JsonSerializable
	
	public function jsonSerialize(): array {
		
		$arr = [
			'code' => $this->code,
			'messages' => [$this->messages],
			'data' => $this->data,
		];
		
		return $arr;
		
	}
	
	
	# / JsonSerializable
	
	# Getters and setters
	
	public function getCode(): int {
		return $this->code;
	}
	
	public function getMessages(): ?array {
		return $this->messages;
	}
	
	public function getData(): ?array {
		return $this->data;
	}
	
	public function getJSON(): ?string {
		return json_encode($this) ?? null;
	}

	public function getString(): ?string {
		return implode($this->data) ?? null;
	}
	
	public function getPrintable(): ?string {

		switch($this->format) {

			case ResponseFormat::JSON:
				$response = $this->getJSON();
				break;
			
			case ResponseFormat::XML:
			case ResponseFormat::HTML:
				$response = $this->getString();
				break;
			
			default:
				$response = '';
			
		}

		return $response;

	}

	public function setCode(int $pCode): bool {
		$this->code = $pCode;
		return true;
	}
	
	public function setMessages(array $pMessages): bool {
		$this->messages = $pMessages;
		return true;
	}
	
	public function addMessage(string $pMessage): int {
		$this->messages[] = $pMessage;
		end($this->messages);
		return key($this->messages);
	}

	public function setData(array $pData): bool {
		$this->data = $pData;
		return true;
	}
	
	public function addData(array $pData): bool {
		$this->data = array_merge($this->data, $pData);
		return true;
	}
	
	# / Getters and setters
	
}

?>
