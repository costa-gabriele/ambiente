<?php namespace _\Database;

use _\Utilities as _UT;

class MysqlDB {
	
	protected
		$connection,
		$fConnected,
		$connectionString,
		$connectionUser,
		$connectionPassword,
		$connectionOptions
	;
	
	public function __construct(string $pHost, string $pDbName, string $pUser, ?string $pPassword, string $pCharSet = null, array $pOptions = []) {
		
		$this->connectionString = 'mysql:host=' . $pHost . ';dbname=' . $pDbName . ';charset=' . $pCharSet;
		$this->connectionUser = $pUser;
		$this->connectionPassword = $pPassword;
		$this->connectionOptions = $pOptions ?? [
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_AUTOCOMMIT => 0
		];

	}
		
	public function connect(): bool {
		try {
			$this->connection = new \PDO($this->connectionString, $this->connectionUser, $this->connectionPassword, $this->connectionOptions);
			$this->fConnected = true;
		} catch(\PDOException $e) {
			$this->fConnected = false;
			_UT\Logger::writeLog('DATABASE CONNECTION', $e->getMessage());
		}

		return $this->fConnected;

	}
	
	public function disconnect(): bool {

		$this->connection = null;
		$this->fConnected = false;

		return !$this->fConnected;

	}
	
	public function callProcedure(string $pProcedureName, array $pParamIn, array &$pParamOut): _UT\Response {
		
		/*
		 * Both input arguments and output parameters must be passed as associative arrays.
		 * However, the keys of the input arguments are irrelevant (they don't have to match
		 * the parameter names of the procedure) and the order of the arguments must
		 * correspond to the order of the parameters declared in the procedure.
		 * The array of the output parameters is overwritten with the returned values.
		 */
		
		$response = new _UT\Response();

		if($this->fConnected) {
			
			$procedureName = $pProcedureName;
			$response->setData(['argsIn' => $pParamIn]);
			
			$sqlCall = 'CALL ' . $procedureName . '(';
			foreach($pParamIn as $paramKey => $paramValue)
				$sqlCall .= ':' . $paramKey . ', ';
			$sqlOutput = 'SELECT ';
			foreach($pParamOut as $paramKey => $paramValue) {
				$sqlVar = '@' . $paramKey;
				$sqlCall .= $sqlVar . ', ';
				$sqlOutput .= $sqlVar . ' AS ' . $paramKey . ', ';
			}
			$sqlCall = substr($sqlCall, 0, -2).')';
			$sqlOutput = substr($sqlOutput, 0, -2);
			$preparedCall = $this->connection->prepare($sqlCall);

			if(!$preparedCall) { # Preparation error
				$response->setCode(2);
			} else {

				try {
					$this->connection->beginTransaction();
					$execution = $preparedCall->execute($pParamIn);
					$output = $this->connection->query($sqlOutput);
					$pParamOut = $output->fetch(\PDO::FETCH_ASSOC);
					try {
						$this->connection->commit();
					} catch(\Exception $e) {
						null;
					}
					$response->setCode(0);
				} catch (\PDOException $pe) { # Error during the execution or while retrieving the output
					try {
						$this->connection->rollBack();
					} catch(\Exception $e) {
						null;
					}
					$response->setCode(3);
					$response->addMessage('An error occurred during the execution of the procedure: ' . $pe->getMessage());
				}
				
			}
			
		} else {
			
			$response->setCode(1);
			$response->addMessage('Not connected');

		}
		
		return $response;
		
	}
	
	public function query(string $pStatement, array $pData = [], $pFetchFormat = \PDO::FETCH_ASSOC, $pFetchOption = null): _UT\Response {
		
		$response = new _UT\Response();
		
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

	# Getters and setters

	public function isConnected(): bool {
		return $this->fConnected;
	}

	# / Getters and setters
	
}

?>
