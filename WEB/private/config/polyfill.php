<?php

if(!function_exists('array_key_first')) {
	function array_key_first(array $pArray) {
		if(!empty($pArray)) {
			foreach($pArray as $key => $value) {
				return $key;
			}
		} else
			return null;
	}
}

if(!function_exists('array_key_last')) {
	function array_key_last(array $pArray) {
		if(!empty($pArray))
			return key(array_slice($pArray, -1, null, true));
		else
			return null;
	}
}

if(!function_exists('mb_str_split')) {
	function mb_str_split(string $pString) {
		return(preg_split('//u', $pString, -1, PREG_SPLIT_NO_EMPTY));
	}
}

?>
