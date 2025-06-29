<?php namespace _\Auth;

abstract class AbstractSession {
	
	protected static $fNew = true;

	public static function init(string $pName = null, string $pKey = null, int $pLifetime = null): bool {
		
		$name = $pName ?? DEFAULT_SESSION_NAME ?? 'session';
		$key = $pKey;
		$lifetime = $pLifetime ?? DEFAULT_SESSION_LIFETIME ?? (60 * 60);

		session_name($name);
		if(!empty($key)) {
			session_id($key);
		}

		session_set_cookie_params($lifetime, URI_ROOT);

		$fStarted = (session_status() != PHP_SESSION_ACTIVE) ? session_start() : false;

		if($fStarted) {

			if(empty($_COOKIE[session_name()]) || $_COOKIE[session_name()] != self::getKey()) { # Newly created session
				self::$fNew = true;
				self::set('sessionName', $name);
				self::set('sessionKey', session_id());
				self::set('sessionStartDateTime', time());
				self::set('sessionLifetime', $lifetime);
				self::set('sessionIpAddress', $_SERVER['REMOTE_ADDR']);
			} else { # Existing session
				self::$fNew = false;
				setcookie(session_name(), session_id(), time() + $lifetime, URI_ROOT);
			}

		}

		return $fStarted;

	}

	protected static function reset(): bool {
		$_SESSION[SESSION_ARRAY_KEY] = [];
		setcookie(session_name(), '', time()-1, URI_ROOT);
		return session_destroy();
	}

	# Getters and setters
	
	public static function get(string $pKey) {
		return $_SESSION[SESSION_ARRAY_KEY][$pKey] ?? null;
	}
	
	protected static function isNew(): bool {
		return self::$fNew;
	}

	public static function getName(): ?string {
		return self::get('sessionName');
	}

	public static function getKey(): ?string {
		return self::get('sessionKey');
	}

	public static function getStartDateTime(): ?int {
		return self::get('sessionStartDateTime');
	}

	public static function getLifetime(): ?int {
		return self::get('sessionLifetime');
	}

	public static function getIpAddress() {
		return self::get('sessionIpAddress');
	}

	public static function getStatus(): int {
		return session_status();
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
