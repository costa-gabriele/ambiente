<?php namespace _\Network;

use _\Utilities as _UT;

class WebSocketServer {
	
	protected
		$logger,
		$host,
		$port,
		$socket,
		$fActive,
		$error,
		$clientSocketIdx = 0,
		$clientSockets,
		$clientData,
		$selectTimeoutMs,
		$bufferSize,
		$read,
		$write,
		$except,
		$keyString
	;
	
	const OPCODES = [
		'CONTINUATION'     => 0,
		'TEXT'             => 1,
		'BINARY'           => 2,
		'CONNECTION_CLOSE' => 8,
		'PING'             => 9,
		'PONG'             => 10
	];

	public function __construct(string $pHost, int $pPort) {
		
		$this->logger = new _UT\Logger('websocket.log');

		$this->setHost($pHost);
		$this->setPort($pPort);

		$this->clientSockets = [];
		$this->clientData = [];
		$this->read = [];
		$this->write = [];
		$this->except = [];
		$this->selectTimeoutMs = 10;
		$this->bufferSize = 1024;
		$this->keyString = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_bind($this->socket, $this->host, $this->port);
		socket_listen($this->socket);
		
	}
	
	public function start(): bool {
		
		if($this->fActive)
			return false;
		
		$this->fActive = true;
		$this->logger->write('START', "Websocket server started on {$this->host}:{$this->port}");
		
		while($this->fActive) {
			
			if(($this->select() > 0) && (in_array($this->socket, $this->read))) {
				$newClientIdx = $this->connectClient();
				$clientReadIdx = array_search($this->socket, $this->read);
				unset($this->read[$clientReadIdx]);
			}

			# Loop through all changed sockets
			foreach($this->read as $changedSocket) {

				$nBytesReceived = socket_recv($changedSocket, $rawData, $this->bufferSize, 0);
				
				# Disconnect inactive clients
				if($nBytesReceived === false) {
					$this->disconnectClient($changedSocket);
					continue;
				}

				$changedSocketIdx = $this->getClientIdx($changedSocket);

				if($changedSocketIdx === $newClientIdx) {

					# Current client has just connected: auth and handshake

					$headers = $this->parseRequestHeaders($changedSocket, $rawData);
					
					if($this->auth($changedSocket)) {
						$this->handshake($changedSocket, $headers);
						$this->postHandshake($changedSocket, $headers);
					} else {
						$this->refuseUnauthorizedConnection($changedSocket);
					}

					$newClientIdx = null;

				} elseif($nBytesReceived) {

					/*
					 * Current client has sent data:
					 * get opcode and behave accordingly
					 */

					$metadata = $this->parsePayloadMetadata($rawData);
					$opcode = $metadata['opcode'];

					switch($opcode) {

						case self::OPCODES['TEXT']:
							$msgReceived = $this->unmask($rawData);
							$this->respond($changedSocket, $msgReceived);
							break;
						
						case self::OPCODES['PING']:
							$msgReceived = $this->unmask($rawData);
							$this->logger->write('PING', "Client idx: {$changedSocketIdx}" . PHP_EOL . "Message: {$msgReceived}");
							$this->pong($changedSocket, $msgReceived);
							break;
						
						case self::OPCODES['PONG']:						
							$msgReceived = $this->unmask($rawData);
							$pingData = $this->getClientData($changedSocket, '_')['PING'] ?? null;
							$pingMessage = $pingData['MESSAGE'] ?? null;
							$this->logger->write('PONG', "Client idx: {$changedSocketIdx}" . PHP_EOL . "Message: {$msgReceived}");
							if($msgReceived == $pingMessage) {
								$pingData['PONG_TIMESTAMP'] = time();
								$this->setClientData($changedSocket, '_', ['PING' => $pingData], true);
							}
							break;
						
						case self::OPCODES['CONNECTION_CLOSE']:
							$this->disconnectClient($changedSocket);
							break;

						default:
							null;
						
					}
					
				}

			}

		}

		$this->logger->write('STOP', 'Websocket server stopped.');
		
	}
	
	public function stop(): bool {
		
		$this->fActive = false;
		return !$this->fActive;
		
	}
	
	protected function select(): int {
		
		$this->read = $this->clientSockets;
		$this->read[] = $this->socket;

		$nChangedSockets = socket_select($this->read, $this->write, $this->except, 0, $this->selectTimeoutMs);
		
		if($nChangedSockets === false) {
			$this->error = socket_strerror(socket_last_error()) . "\n";
			$this->logger->write('SELECT', $this->error);
			$nChangedSockets = -1;
		}
		
		return $nChangedSockets;
		
	}
	
	protected function getClientIdx($pClientSocket): ?int {
		$clientIdx = array_search($pClientSocket, $this->clientSockets);
		return $clientIdx;
	}

	protected function getClientData($pClientSocket, string $pKey): mixed {

		$clientIdx = $this->getClientIdx($pClientSocket);
		return $this->clientData[$clientIdx][$pKey] ?? null;

	}

	protected function setClientData($pClientSocket, string $pKey, $pData, bool $pfAdd = false): bool {

		$clientIdx = $this->getClientIdx($pClientSocket);

		if(isset($this->clientData[$clientIdx])) {

			if($pfAdd) {
				$this->clientData[$clientIdx][$pKey] = array_merge (
					is_array($this->clientData[$clientIdx][$pKey]) ? $this->clientData[$clientIdx][$pKey] : [$this->clientData[$clientIdx][$pKey]],
					is_array($pData) ? $pData : [$pData]
				);
			} else
				$this->clientData[$clientIdx][$pKey] = $pData;
			return true;

		} else
			return false;
		
	}

	protected function getRequestUri($pClientSocket): ?string {
		return $this->getClientData($pClientSocket, '_')['URI'];
	}

	protected function getRequestQueryStringData($pClientSocket): ?array {
		return $this->getClientData($pClientSocket, '_')['QUERY_STRING_DATA'];
	}

	protected function connectClient(): int {
		
		$clientSocket = socket_accept($this->socket);
		$clientIdx = $this->clientSocketIdx++;
		$this->clientSockets[$clientIdx] = $clientSocket;
		$this->clientData[$clientIdx] = [];

		$this->logger->write('CONNECTION', "Client {$clientIdx} succesfully connected");

		end($this->clientSockets);
		return key($this->clientSockets);

	}

	protected function disconnectClient($pClientSocket): bool {
		
		$clientIdx = $this->getClientIdx($pClientSocket);
		if($clientIdx !== false && isset($this->clientSockets[$clientIdx])) {
			unset($this->clientSockets[$clientIdx]);
			unset($this->clientData[$clientIdx]);
			socket_close($pClientSocket);
			$this->logger->write('DISCONNECTION', "Client {$clientIdx} disconnected");
			return true;
		} else
			return false;
		
	}

	protected function parseRequestHeaders($pClientSocket, string $pRawData): array {

		$headers = [];
		$rawDataRows = explode("\r\n", $pRawData);

		foreach($rawDataRows as $rowIdx => $rawDataRow) {

			if($rowIdx == 0) { # First row, parse request URI

				$reqData = explode(' ', $rawDataRow);

				if(strtoupper(trim($reqData[0])) == 'GET') {

					$reqParts = explode("?", $reqData[1]);
					$uri = $reqParts[0];
					$queryString = $reqParts[1] ?? '';
					$queryStringArray = [];
					foreach(explode("&", $queryString) as $kv) {
						$kva = explode("=", $kv);
						if(count($kva) == 2)
							$queryStringArray[$kva[0]] = $kva[1];
					}

					$reqInfo = ['URI' => $uri, 'QUERY_STRING_DATA' => $queryStringArray];
					$this->setClientData($pClientSocket, '_', $reqInfo);

				}

				continue;

			}

			$headerKeyValue = explode(': ', $rawDataRow);
			if(count($headerKeyValue) == 2)
				$headers[$headerKeyValue[0]] = $headerKeyValue[1];

		}

		return $headers;

	}

	# Override this method to manage authorization/authentication
	protected function auth($pClientSocket): bool {
		#$requestUri = $this->getRequestUri($pClientSocket);
		#$queryStringData = $this->getRequestQueryString($pClientSocket));
		return true;
	}

	protected function handshake($pClientSocket, array $pHeaders): bool {

		if(isset($pHeaders['Sec-WebSocket-Key'])) {

			$secKey = $pHeaders['Sec-WebSocket-Key'];
			$secAccept = base64_encode(pack('H*', sha1($secKey . $this->keyString)));
			$response = "HTTP/1.1 101 Switching Protocols\r\n" .
				"Upgrade: websocket\r\n" .
				"Connection: Upgrade\r\n" .
				"Sec-WebSocket-Accept: {$secAccept}\r\n" .
				"\r\n"
			;
			socket_write($pClientSocket, $response);
			return true;
		} else {
			$this->error = 'Sec-WebSocket-Key header not available.';
			$this->logger->write('ERROR', $this->error);
			return false;
		}

	}

	# Override this method to handle post-handshake operations
	protected function postHandshake($pClientSocket, array $pHeaders = []) {
		null;
	}

	protected function refuseUnauthorizedConnection($pClientSocket): bool {

		$response = "HTTP/1.1 401 Unauthorized\r\n\r\n";
		socket_write($pClientSocket, $response);
		return $this->disconnectClient($pClientSocket);

	}

	protected function parsePayloadMetadata(string $pPayload): array {

		/*
		 * First byte contains message metadata:
		 * Bit 0: FIN (continuation)
		 * Bits 4-7: OPCODE
		 */

		$firstByte = ord($pPayload[0]);
		$fin = $firstByte >= 128 ? 1 : 0;
		$opcode = $firstByte & 15;

		$metadata = [
			'fin' => $fin,
			'opcode' => $opcode
		];

		return $metadata;

	}

	protected function mask(string $pMessage, int $pOpcode = self::OPCODES['TEXT']): string {

		$msgLength = strlen($pMessage);
		$frame = [];
		$frame[0] = 128 + $pOpcode;
		$frame[1] = $msgLength <= 125 ? $msgLength : (125 + (($msgLength <= 65535) ? 1 : 2));
		
		$j = ($msgLength <= 125) ? 1 : (($msgLength <= 65535) ? 3 : 9);

		for($i = 2; $i <= $j; $i++)
			$frame[$i] = ($msgLength >> (($j - $i) * 8)) & 255;

		foreach(str_split($pMessage) as $char)
			$frame[] = ord($char);
		
		$maskedMessage = implode(array_map('chr', $frame));
		return $maskedMessage;

	}

	protected function unmask(string $pPayload): ?string {

		$payloadLength = ord($pPayload[1]) & 127;

		$maskStart = ($payloadLength == 126) ? 4 : (($payloadLength == 127) ? 10 : 2);
		$masks = substr($pPayload, $maskStart, 4);
		$data = substr($pPayload, $maskStart + 4);
		
		$unmaskedMessage = '';

		for($i = 0; $i < strlen($data); $i++)
			$unmaskedMessage .= $data[$i] ^ $masks[$i % 4];

		return $unmaskedMessage;

	}

	protected function write($pClientSocket, string $pMessage): int|bool {

		$maskedMessage = $this->mask($pMessage);
		return socket_write($pClientSocket, $maskedMessage, strlen($maskedMessage));

	}

	protected function broadcast(string $pMessage, array $pExcludeSockets = []) {

		foreach($this->clientSockets as $clientSocket) {
			if(!in_array($clientSocket, $pExcludeSockets)) {
				$this->write($clientSocket, $pMessage);
			}
		}

	}

	protected function ping($pClientSocket, string $pMessage): int|bool {

		$maskedMessage = $this->mask($pMessage, self::OPCODES['PING']);
		if($r = socket_write($pClientSocket, $maskedMessage, strlen($maskedMessage))) {
			$pingData = [
				'TIMESTAMP' => time(),
				'MESSAGE' => $pMessage,
				'PONG_TIMESTAMP' => null
			];
			$this->setClientData($pClientSocket, '_', ['PING' => $pingData], true);
		}
		return $r;

	}

	protected function pong($pClientSocket, string $pMessage): int|bool {
		$maskedMessage = $this->mask($pMessage, self::OPCODES['PONG']);
		return socket_write($pClientSocket, $maskedMessage, strlen($maskedMessage));
	}

	# Override this method to handle the response
	protected function respond($pSocket, string $pMessage) {
		$this->broadcast($pMessage, [$pSocket]);
	}

	# Getters and setters
	
	public function getLogFilePath(): ?string {
		return $this->logger->getFilePath();
	}

	public function isLogPrinting(): bool {
		return $this->logger->isPrinting();
	}

	public function getHost(): string {
		return $this->host;
	}
	
	public function getPort(): int {
		return $this->port;
	}

	public function getSelectTimeoutMs(): int {
		return $this->selectTimeoutMs;
	}
	
	public function getBufferSize(): int {
		return $this->bufferSize;
	}

	public function setLogFilePath(string $pLogFilePath): bool {
		return $this->logger->setFilePath($pLogFilePath);
	}

	public function setLogPrinting(bool $pfLogPrinting): bool {
		return $this->logger->setPrinting($pfLogPrinting);
	}

	public function setHost(string $pHost): bool {
		$this->host = $pHost;
		return true;
	}

	public function setPort(string $pPort): bool {
		$this->port = $pPort;
		return true;
	}
	
	public function setSelectTimeoutMs(int $pSelectTimeoutMs): bool {
		$this->selectTimeoutMs = $pSelectTimeoutMs;
		return true;
	}

	public function setBufferSize(int $pBufferSize): bool {
		$this->bufferSize = $pBufferSize;
		return true;
	}

	# / Getters and setters

}

?>
