<?php namespace _\Auth;

class Session {
	
	protected
		$name,
		$lifetime
	;
	
	public function __construct(string $pName = DEFAULT_SESSION_NAME, int $pLifetime = DEFAULT_SESSION_LIFETIME) {
		
		$this->setName($pName);
		$this->setLifetime($pLifetime);
		
		session_name($this->name);
		session_start();
		
		if(empty($_COOKIE[$this->name]) || empty($_SESSION[SESSION_ARRAY_KEY])) {
			
			if(session_status() == PHP_SESSION_ACTIVE) {
				session_destroy();
				setcookie($this->name, '', time()-1);
			}
			
			ini_set('session.cookie_lifetime', $this->lifetime);
			ini_set('session.gc-maxlifetime', $this->lifetime);
			session_start();
			
		}
	
	}
	
	# Getters and setters
	
	public function get(string $pKey) {
		return $_SESSION[SESSION_ARRAY_KEY][$pKey];
	}

	public static function getName(): string {
		return $this->name;
	}
	
	public static function getKey(): string {
		return $_SESSION[SESSION_ARRAY_KEY]['key'];
	}
	
	public function set(string $pKey, $pValue): bool {
		$_SESSION[SESSION_ARRAY_KEY][$pKey] = $pValue;
		return true;
	}

	public function setName(string $pName): bool {
		$this->name = $pName;
		return true;
	}

	public function setLifetime(int $pLifetime): bool {
		$this->lifetime = $pLifetime;
		return true;
	}

}

?>
