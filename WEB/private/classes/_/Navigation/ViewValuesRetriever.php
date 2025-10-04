<?php namespace _\Navigation;

use _\Utilities as _UT;

abstract class ViewValuesRetriever {
	
	protected $request;
	
	public function __construct(_UT\Request $pRequest) {
		$this->request = $pRequest;
	}

	abstract function retrieve(): array;

}

?>
