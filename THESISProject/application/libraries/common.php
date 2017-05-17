<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common {
	/*
	Check Null or Empty
	*/
	function checkNullOrEmpty($varInput){
		return (!isset($varInput) || empty($varInput));
	}

	/*
	$key = errorCode;
	$value = lineNo;

	map[string,mixed] 	-- The key is undefined
	$key(string) 		-- The key is defined, but isn't yet set to an array
	$value(string) 		-- The key is defined, and the element is an array.
	*/
	function appendThings($array, $key, $value){
		if(empty($array[$key]) && !isset($array[$key])){
			$array[$key] = array(0 => $value);
		}else{ //(is_array($array[$key]))
			if(array_key_exists($key, $array)){
				$array[$key][] = $value;
			}
		}
		return $array;
	}

	function nullToEmpty($value){
		if(NULL == $value)
			return "";
		return $value; 
	}
}

?>