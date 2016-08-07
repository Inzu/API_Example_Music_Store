<?php

$response = new stdClass();
$response->msg = "";
$response->cart_size = 0;

///The cart info is stored in a two dimensional serialised array using a cookie called "cart"

$item_code = preg_replace("/[^a-zA-Z0-9_]/", "", @$_REQUEST['item_code']);
$price = preg_replace("/[^a-z0-9._]/", "", @$_REQUEST['price']);
$cart = preg_replace("/[^a-z0-9,{};:\".]/", "", @$_COOKIE["cart"]); 

$quantity= ereg_replace("[^0-9]+", "", $_REQUEST['quantity']);


if(!$cart){
	$cart = array();
}else{
	$cart = unserialize($cart);
}


	//Adjust quanity (from edit cart)
	
	if($quantity!=""){
		
		//increase quantity
		foreach($cart as $item =>$val){
			if($item==$item_code){
			$cart[$item]['quantity']=$quantity;
			}
		}
	
	
		if($quantity==0){
		unset($cart[$item_code]);
		}
	
	$cart=serialize($cart);
	setcookie("cart", "$cart",0,"/");
	
	header("Location: cart_edit.php");
	exit();
	}



	///AJAX add item
	
	
	
	//If a new item add to array
	
	if(!$cart[$item_code] && $item_code!=""){
		$cart[$item_code]['quantity'] = 0; //Quantity added in following loop
		$cart[$item_code]['price'] = $price;
	}
	
	
	//increase quantity and create totals
	
	foreach($cart as $item => $val){
		
		if($item == $item_code && $item_code!=""){
			$cart[$item]['quantity']++;
			$response->msg = "Item added";
		}
		
		$response->cart_size += $cart[$item]['quantity'];
		$response->cart_total += $cart[$item]['quantity'] * $cart[$item]['price'];
		
		for($i=0; $i<$cart[$item]['quantity']; $i++){
		$response->cart_items .= "=".$item;
		}
	}
	
	$cart=serialize($cart);
	
	setcookie("cart", "$cart",0,"/");
	
	$response->cart_total = number_format($response->cart_total,2,'.',',');
	
	$response->success = true;
	

exit(json_encode($response));

?>