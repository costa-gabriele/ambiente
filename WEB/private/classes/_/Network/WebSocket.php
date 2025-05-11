<?php namespace _\Network;

class WebSocket {
	
	protected
		$address,
		$port,
		$socket,
		$fActive,
		$error,
		$clientSockets
	;
	
	public function __construct(string $pAddress, int $pPort) {
		
		$this->setAddress($pAddress);
		$this->setPort($pPort);
		$this->clientSockets = [];
		
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_bind($this->socket, $this->host, $this->port);
		socket_listen($this->socket);
		
		
	}
	
	public function start(): bool {
		
		if($this->fActive)
			return false;
		
		$this->fActive = true;
		
		while($this->fActive) {
			
			$this->connectClient();
			
		}
		
	}
	
	public function stop(): bool {
		
		$this->fActive = false;
		return true;
		
	}
	
	protected function select(): int {
		
		$read = $this->clientSockets;
		
		$nChangedSockets = socket_select();
		
		if($nChangedSockets === false) {
			$this->error = socket_strerror(socket_last_error()) . "\n";
			$nChangedSockets = -1;
		}
		
		return $nChangedSockets;
		
	}
	
	protected function connectClient() {
		$clientSocket = socket_accept($this->socket);
		$this->clientSockets[] = $clientSocket;
	}

	# Getters and setters
	
	public static function getAddress(): string {
		return $this->address;
	}
	
	public static function getPort(): int {
		return $this->port;
	}
	
	public function setAddress(string $pAddress): bool {
		$this->address = $pAddress;
		return true;
	}

	public function setPort(string $pPort): bool {
		$this->port = $pPort;
		return true;
	}
	
	# / Getters and setters

}

?>
