<?php

use _\Navigation as _NV;

class ExampleRetriever extends _NV\ViewValuesRetriever {
	
	function retrieve(): array {

		return ['list' => ['a','b','c']];
		
	}

}

?>
