<?php namespace _\Auth;

class User {
	
	protected
		$name
	;
	
	public function __construct(string $pName = null) {
		
		$this->setName($pName);
	
	}
	
	# Getters and setters
	
	public static function getName(): string {
		return $this->name;
	}

	public function setName(string $pName): bool {
		$this->name = $pName;
		return true;
	}

	# / Getters and setters

}

?>
