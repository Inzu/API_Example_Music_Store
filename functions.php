<?php

function INZU_GET($end_point, $args, $return = false){

	$url = API_BASE.API_VERSION."/".$end_point."?".http_build_query($args);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_USERPWD, API_KEY . ":" . API_PASS);
	$output = curl_exec($ch);
	
	curl_close($ch);

	if ( !$return ) {
		
		return json_decode($output);
	
	} else if ( $return == "raw" ) {
	
		return $output;	
		
	} else if ( $return == "echo" ) {
		
		echo $output;	
		
	}	

}

?>