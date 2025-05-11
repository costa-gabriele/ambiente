<?php namespace _\Database;

class MysqlDB {
	
	protected
		$connection,
		$connectionStatus,
		$connectionString,
		$connectionUser,
		$connectionPassword,
		$connectionOptions
	;
	
	public function __construct($pPDOConnectionString, $pUser, $pPassword, $pOptions) {
		$this->connectionString = $pPDOConnectionString;
		$this->connectionUser = $pUser;
		$this->connectionPassword = $pPassword;
		$this->connectionOptions = $pOptions;
	}
	
	# Private methods
	
	private function connect() {
		try {
			$this->connection = new \PDO($this->connectionString, $this->connectionUser, $this->connectionPassword, $this->connectionOptions);
			$this->connectionStatus = 1;
		} catch(PDOException $e) {
			$this->connectionStatus = 0;
			GL\Logger::writeLog('DATABASE CONNECTION', $e->getMessage());
		}
	}
	
	private function disconnect() {
		$this->connection = null;
		$this->connectionStatus = 0;
	}
	
	private function callProcedure(string $pProcedureName, array $pParamIn, array &$pParamOut): GL\Response {
		
		/*
		 * Both in and out parameters must be passed as associative arrays.
		 * The array of the out parameters is overwritten
		 * by the return array of the function.
		 */
		
		$response = new GL\Response([]);
		$procedureName = $pProcedureName;
		$response->setData(['paramIn' => $pParamIn]);
		
		$this->connect();
		
		$sqlCall = "CALL {$procedureName} (";
		foreach($pParamIn as $paramName => $paramValue)
			$sqlCall .= ":{$paramName}, ";
		$sqlOutput = 'SELECT ';
		foreach($pParamOut as $paramName => $paramValue) {
			$sqlVar = "@{$paramName}";
			$sqlCall .= "{$sqlVar}, ";
			$sqlOutput .= "{$sqlVar} AS {$paramName}, ";
		}
		$sqlCall = substr($sqlCall, 0, -2).')';
		$sqlOutput = substr($sqlOutput, 0, -2);
		$preparedCall = $this->connection->prepare($sqlCall);
		if(!$preparedCall) { # Preparation error
			$response->setCode(1);
		} else {
			try {
				$this->connection->beginTransaction();
				$execution = $preparedCall->execute($pParamIn);
				$output = $this->connection->query($sqlOutput);
				$pParamOut = $output->fetch(\PDO::FETCH_ASSOC);
				$this->connection->commit();
				$response->setCode(0);
			} catch (\PDOException $e) { # Error during the execution or while retrieving the output
				$this->connection->rollBack();
				$response->setCode(1);
				$response->setMessage('An error occurred during the execution of the procedure: ' . $e->getMessage());
			}
			
		}
		
		return $response;
		
	}
	
	private function query(string $pStatement, ?array $pData = [], $pFetchFormat = \PDO::FETCH_ASSOC, $pFetchOption = null): GL\Response {
		
		$response = new GL\Response([]);
		
		if(empty($pData)) {
			
			$sql = $this->connection->query($pStatement);
			
		} else {
		
			$sql = $this->connection->prepare($pStatement);
			$sql->execute($pData);
			
		}
		
		$dataSet = (empty($pFetchOption)) ? $sql->fetchAll($pFetchFormat) : $sql->fetchAll($pFetchFormat, $pFetchOption);
		$response->setData($dataSet);
		$response->setCode(0);
		
		return $response;
		
	}
	
}

?>
