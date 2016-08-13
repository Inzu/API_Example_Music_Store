<?php

//Your API key
$api_key = "02c4e87c154b34b5b06be242a36d54dd";

$api_base = "http://api.inzu.net/1.4";

$pay_url = 'https://payments.inzu.net/'; //Current Inzu payment URL
$pay_callback = 'http://mywebsite.com/complete'; //Your website order completion page

$loc = "us"; //Set the lowercase two character code for currency location (United States)
$currency = "&#36;"; //The currency symbol (Dollars)


//Check API connection can be established or print error
$json = file_get_contents("{$api_base}/general/account_live?api_key={$api_key}");
$data = json_decode($json);

if($data->live_status != "true"){
	
	echo "Sorry the site is down at the moment.";
	exit();
	
}


?>