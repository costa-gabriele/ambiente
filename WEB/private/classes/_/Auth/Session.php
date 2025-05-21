<?php namespace _\Auth;

abstract class AbstractSession {
	
	public static function init(array $pOptions = []) {
		
		$name = $pOptions['name'] ?? DEFAULT_SESSION_NAME;
		$lifetime = $pOptions['lifetime'] ?? DEFAULT_SESSION_LIFETIME;
		
		if(!empty($pOptions['key'])) {
			session_id($pOptions['key']);
		}

		session_name($name);
		session_start();
		
		if(empty($_COOKIE[$name]) || empty(self::getKey())) {
			
			if(session_status() == PHP_SESSION_ACTIVE) {
				session_destroy();
				setcookie($name, '', time()-1);
			}
			
			ini_set('session.cookie_lifetime', $lifetime);
			ini_set('session.gc-maxlifetime', $lifetime);
			session_start();

			self::setName($name);
			self::setKey(session_id());
			self::setLifetime($lifetime);

			self::onCreation();

		}
	
	}
	
	abstract protected static function onCreation(): bool;
	
	# Getters and setters
	
	public static function get(string $pKey) {
		return $_SESSION[SESSION_ARRAY_KEY][$pKey];
	}
	
	public static function getName(): ?string {
		return self::get('sessionName');
	}

	public static function getKey(): ?string {
		return self::get('sessionKey');
	}

	public static function getLifetime(): ?int {
		return self::get('sessionLifetime');
	}

	public static function set(string $pKey, $pValue): bool {
		$_SESSION[SESSION_ARRAY_KEY][$pKey] = $pValue;
		return true;
	}
	
	public static function setName(string $pName): bool {
		return self::set('sessionName', $pName);
	}

	public static function setKey(string $pKey): bool {
		return self::set('sessionKey', $pKey);
	}

	public static function setLifetime(int $pLifetime): bool {
		return self::set('sessionLifetime', $pLifetime);
	}

	# / Getters and setters

}

?>
