<?php

include("functions.php"); 
include("config.php"); 

$cart = preg_replace("/^a-z0-9,{};:\"./", "", @$_COOKIE["cart"]);

$cart_arr = json_decode($cart);

$item_array = array();
$item_count = 0;
$totalprice = 0;

$pay_url .= '?item_code'; 

if ( $cart_arr ) {

	// Create total and form item array
	
	foreach ( $cart_arr->cart as $key => $item ) {	
		
		$item_count += $item->quantity;
		$totalprice += $item->price * $item->quantity;

		for ( $i=0; $i < $item->quantity; $i++ ) {
			
			array_push($item_array, $item->item_code);
			$pay_url .= '='.$item->item_code;
			
		}

	}
}


$item_array = implode(",", $item_array);
$totalprice = number_format($totalprice, 2, '.', ',');

?>
<html>
<head>
	<script type="text/javascript" src="cart.js"></script>
	<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body style="text-align: center">
	<div id="cart-edit">
	<h3>Your items</h3>
	<?php
	
	// Check if there are items in the cart
	
	if ( $item_array ) {
	
		// Inzu API Call to get product info for items
		
		$inzu = INZU_GET("store/cart", array("item_array"=>$item_array));
	
		$cart_display = NULL;
	
		$i = 0;
	
		foreach ($inzu->data as $item) { 
		
	?>
		<form class="cart_item" id="form_main_<?php echo $i; ?>" method="post">
			<table cellspacing="0" cellpadding="0" width="100%" >
				<tr>
					<td valgin="left" width="265"><?php echo $item->title; ?></td>
					<td align="right" valgin="middle" width="80"><?php echo $currency.$item->{'price_'.$loc};?></td>
					<td align="right" valign="middle" width="8"></td>
					<td align="left" valgin="middle" width="30"><input type="text" name="quantity" value="<?php echo $item->quantity; ?>" size="2" maxlength="2" onChange="cart.adjust(<?php echo $item->item_code.",".$i; ?>, this.value)"></td>
					<td align="right" valgin="middle"></td>
					<td align="right" valgin="middle"><a href="javascript: cart.deleteItem(<?php echo $item->item_code.",".$i; ?>)"  class="remove">remove</a></td>
				</tr>
			</table>
		</form>
		<?php 	
			
			$i++; 
			
			} 
		
		?>
		<hr/>
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
		  <tr>
		    <td align="right" valign="middle">Total:&nbsp; <?php echo $currency; ?><span id="cart_total"><?php echo $totalprice; ?></span></td>
		  </tr>
		</table>
		<a id="checkout_link" href="<?php echo $pay_url; ?>">checkout</a>
		<a id="back_link" href="index.php">back</a>
		<div class="update" id="quantity-updated"></div>
		<?php
			
			} else {
			
				echo'<p>You have no items in your cart</p>';
			
			}
			
		?>	
			<script>
				var cart, pay_url ="<?php echo $pay_url; ?>";
				var cart = new Inzu_cartEdit(pay_url); 
			</script>
		</div>
</body>	
</html>