<html>
<head>
<script type="text/javascript" src="add_item.js"></script>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body style="text-align: center">
<div id="cart-edit">
<h3>Your items</h3>


<?php

include("config.php"); 

$cart = preg_replace("/[^a-z0-9,{};:\".]/", "", @$_COOKIE["cart"]);
$cart_arr = unserialize($cart);

$item_count = 0;
$totalprice = 0;

$pay_url.='?item_code'; 

$item_array=array();


//Create total and form item array

foreach($cart_arr as $item  => $val){
	
$item_count += $cart_arr[$item]['quantity'];
$totalprice += $cart_arr[$item]['price'] * $cart_arr[$item]['quantity'];

	for($i=0; $i < $cart_arr[$item]['quantity']; $i++){
		array_push($item_array, $item);
		$pay_url.='='.$item;
	}

}

$item_array=implode(",",$item_array);

$totalprice = number_format($totalprice,2,'.',',');



//Check if there are items in the cart
if($item_array){

//INZU API Call to get product info for items
$json = file_get_contents("$api_base/store/cart?api_key={$api_key}&item_array={$item_array}");
$inzu = json_decode($json);

$cart_display = NULL;

$i=0;

foreach ($inzu->data as $item) { 
	
$i++;

?>


	<form name="form_<?php echo $i; ?>" id="form_main" method="post" action="cart_data.php" >
	
		<input name="item_code" type="hidden" value="<?php echo $item->item_code; ?>" />
	
		<table cellspacing="0" cellpadding="0" width="100%" >
			<tr>
			<td valgin="left" width="265"><?php echo $item->title; ?></td>
			<td align="right"valgin="middle" width="80"><?php echo $currency.$item->price_uk; ?></td>
			<td align="right" valign="middle" width="8"></td>
			<td align="left" valgin="middle" width="30">
			<input type="text" id="item_quantity_<?php echo $i; ?>" name="quantity" value="<?php echo $item->quantity; ?>" size="2" maxlength="2">
			</td>
			<td align="right" valgin="middle">
			<a class="edit" href="javascript:document.form_<?php echo $i; ?>.submit();">edit</a>
			</td>
			<td align="right" valgin="middle">
			<a class="remove" href="javascript:removeItem('<?php echo $i; ?>')">remove</a>
			</td>
			</tr>
		</table>
	
	</form>
	

<?php } ?>


<hr/>

	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
	    <td align="right" valign="middle"  >Total:&nbsp; <?php echo $currency.$totalprice; ?> </td>
	  </tr>
	</table>

<script type="text/javascript">

function removeItem(i){

document.getElementById("item_quantity_"+i).value=0;	
document["form_"+i].submit();
	
}
	
</script>	

<a class="checkout" href="<?php echo $pay_url; ?>">checkout</a>

<?php

}else{

echo'<p>You have no items in your cart</p>';

}

?>


</div>
</body>	
</html>