<?php namespace _\Auth;

class User {
	
	protected
		$session,
		$name
	;
	
	public function __construct(Session $pSession, string $pName = null) {
		
		$this->setSession($pSession);
		$this->setName($pName);
	
	}
	
	# Getters and setters
	
	public static function getSession(): Session {
		return $this->session;
	}
	
	public static function getName(): string {
		return $this->name;
	}

	public function setSession(Session $pSession): bool {
		$this->session = $pSession;
		return true;
	}

	public function setName(string $pName): bool {
		$this->name = $pName;
		return true;
	}

}

?>
