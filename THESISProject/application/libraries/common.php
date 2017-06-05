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
		if(NULL == $value || empty($value))
			return "";
		return $value; 
	}

	function postCURL($_url, $_param){        
        $this->http_build_query_for_curl($_param, $postData);
        
        //test
       	//var_dump($postData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));

        //test
        //echo (is_callable('curl_init')) ? '<h1>Enabled</h1>' : '<h1>Not enabled</h1>' ;
        
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function http_build_query_for_curl($arrays, &$new = array(), $prefix = null) {
	    if ( is_object( $arrays ) ) {
	        $arrays = get_object_vars( $arrays );
	    }

	    foreach ( $arrays AS $key => $value ) {
	        $k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
	        if ( is_array( $value ) OR is_object( $value )  ) {
	            $this->http_build_query_for_curl( $value, $new, $k );
	        } else {
	            $new[$k] = $value;
	        }
	    }
	}

}

?>