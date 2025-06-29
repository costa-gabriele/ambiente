<?php namespace _\Network;

class ServerSentEvent {

    protected
        $headers = [],
        $fRunning = false,
        $timeLimit = 0,
        $cycleSleep = 1
    ;

    public function __construct() {

        $this->addHeader('Content-Type', 'text/event-stream');
        $this->addHeader('Connection', 'keep-alive');
        $this->addHeader('Cache-Control', 'no-cache');

    }
    
    public function run() {

        $this->fRunning = true;

        $this->writeHeaders();

        while($this->fRunning) {

            $this->output();

			if(ob_get_Contents())
            	ob_end_flush();
			
            flush();

			if(connection_aborted())
				break;
			
            sleep($this->cycleSleep);

        }

    }

    public function stop(): bool {
        $this->fRunning = false;
        return !$this->fRunning;
    }

	protected function writeHeaders() {
        
		foreach($this->headers as $key => $values) {
			foreach($values as $val) {
				header($key . ': ' . $val);
			}
		}

        set_time_limit($this->timeLimit);

    }

    protected function writeEvent(string $pEventName, string $pEventData): bool {
        echo 'event: ' . $pEventName . "\n";
        echo 'data: ' .  $pEventData . "\n";
        echo "\n";
        return true;
    }

	# Override this method to produce your output
    protected function output() {
        $this->writeEvent('test2', (new \DateTime())->format('d/m/Y H:i:s'));
    }

    # Getters and setters

	public function getHeaders(): array {
        return $this->headers;
    }

    public function isRunning(): bool {
        return $this->fRunning;
    }

    public function getTimeLimit(): int {
        return $this->timeLimit;
    }

    public function getCycleSleep(): int {
        return $this->cycleSleep;
    }

	public function addHeader(string $pKey, string $pValue, bool $pfMulti = false): int {
		
		if($pfMulti && !empty($this->headers[$pKey])) { # Multiple value header
			$this->headers[$pKey][] = $pValue; 
		} else { # Single value header
			$this->headers[$pKey] = [$pValue];
		}

		return count($this->headers);

	}

    public function setTimeLimit(int $pTimeLimit): bool {
        $this->timeLimit = $pTimeLimit;
        return true;
    }

    public function setCycleSleep(int $pCycleSleep): bool {
        $this->cycleSleep = $pCycleSleep;
        return true;
    }

    # / Getters and setters
}

?>
