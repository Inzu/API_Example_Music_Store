<?php


include("config.php"); 


session_start();


//We store information such as format and cat no is session variables so that when an item is added 
//the correct product is displayed after the user is redirected back to the shop.

//Get the format selected by the user and store in session variable 
$format=preg_replace("/[^a-zA-Z0-9_]/", "", @$_REQUEST['format']);
if($format){
$_SESSION['format']=$format;
}

$format=@$_SESSION['format'];


//Cat no refers to a release's catalogue number - this is the same for all the formats in a release
$cat_no=preg_replace("/[^a-zA-Z0-9_]/", "", @$_REQUEST['cat_no']);
if($cat_no){
$_SESSION['cat_no']=$cat_no;
}



//Featured release

//If a release has been selected use "cat no" to get the data from INZU otherwise just select the latest release
if($cat_no){
$json = file_get_contents("http://api.inzu.net/1.4/store/music?api_key={$api_key}&cat_no={$cat_no}&format={$format}");
}else{
$json = file_get_contents("http://api.inzu.net/1.4/store/music?api_key={$api_key}&latest=true&format={$format}");
}

$inzu = json_decode($json); 



/*
Format list - Get the list of formats available for this release and make links to change the format whilst passing the cat no in the URL.
*/

$format_array=explode(',',$inzu->data[0]->format_array); /// Turn comma separated format list into an array

foreach($format_array as $keys=>$val){
$format_links.=<<<EOD
<a href="index.php?cat_no={$inzu->data[0]->cat_no}&format={$val}" >$val</a>&nbsp;
EOD;
}



//Now create track list for the featured release and bundle information

$i=0;
foreach ($inzu->data[0]->track as $track) { 


$price = $track->{'price_'.$loc};

//The track marked as "bundle" is used to retrieve information such as bundle price and bundle title

if($track->number=="bundle"){

//Create HTML for featured release bundle information
$featured=<<<EOD
<div>
    <img src="{$inzu->data[0]->image}" width="200" />
    <h2>{$track->title}</h2>
    <p>{$track->artists}<br />
    {$inzu->data[0]->short_description}
    </p>
</div>

<div class="shopPriceFeatured"><p><strong>Price: {$currency}{$price}</strong></p></div>
<div class="formats">Formats: $format_links</div>

<div class="buy_button" style="float:right" >
<!--The price information and buy button must be in the same container!-->
<input name="item_code" type="hidden" value="{$track->item_code}" />
<input name="price" type="hidden" value="{$price}" />
<a class="button buy" href="javascript: void(0);" onClick="store_cart.updateCart(this)" >BUY</a>
</div>
EOD;
}



//Track list for featured release


//If a preview is available attach a preview button

if($track->preview!=""){
$audio_button=<<<EOD

<script type="text/javascript">

var controlBtn = '<audio id="audiotag{$i}" preload="none"><source src="{$track->preview}" type="audio/mpeg"></audio><div class="item-btn item-preview"><div class="item-btn-txt" ><a class="button play" href="javascript: playSound.trigger(\'{$i}\')"><span id="control_btn{$i}">PLAY</span></a></div></div>';

var myAudio = document.createElement('audio'); 

document.write(controlBtn); 

</script>
EOD;
}else{
$audio_button=NULL;
}


//Build track list leaving out the bundle

if($track->number!="bundle"){


//Only include buy button and price for each track if format is Digital
if($inzu->data[0]->format=="Digital"){

$price_info ="- <strong>{$currency}{$price}</strong>";

$buy=<<<EOD
<td width="40" align="left" >
<div>
<!--The price information and buy button must be in the same container!-->
<input name="item_code" type="hidden" value="{$track->item_code}" />
<input name="price" type="hidden" value="{$price}" />
<a class="button buy" href="javascript: void(0);" onClick="store_cart.updateCart(this)">BUY</a>
</div>
</td>
EOD;

}



$i++;

$track_list.=<<<EOD
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="17">
  <tr height="33">
    <td class="shopDes" >$i. {$track->title} $price_info</td>
    <td width="40" align="left" >$audio_button</td>
    $buy
  </tr>
</table>
EOD;


}
}

//End featured release




//List of first 16 available releases, only displaying bundle information

$json = file_get_contents("$api_base/store/music?api_key={$api_key}&page=1&page_rows=16&release=true");
$inzu = json_decode($json); 

foreach ($inzu->data as $product) { 

$price = $product->track[0]->{'price_'.$loc};


//Create format links
$format_links=NULL;

$format_array=explode(',',$product->format_array);

foreach($format_array as $keys=>$val){
$format_links.=<<<EOD
<a href="index.php?cat_no={$product->cat_no}&format={$val}">$val</a>&nbsp;
EOD;
}


$format_links=<<<EOD
<div class="formats">
Formats: $format_links
</div>
EOD;



$more_releases.=<<<EOD
<div class="item more">

	<div>
	    <div class="img"><img src="{$product->image_thumb}" /></div>
	    <h3>{$product->bundle_title}</h3>
	    <p>{$product->short_description}</p>
	    <p class="price"><strong>Price: {$currency}{$price}</strong></p>
	</div>

	$format_links

    <div>
    <a class="button view" href="index.php?cat_no={$product->cat_no}&format={$product->format}">+ view tracks</a>
    <!--The price information and buy button must be in the same container!-->
	<input name="item_code" type="hidden" value="{$product->track[0]->item_code}" />
	<input name="price" type="hidden" value="{$price}" />
    <a class="button buy" href="javascript: void(0);" onClick="store_cart.updateCart(this)">BUY</a>
    </div>
</div>
EOD;


}



?>

<html>
<head>
<script type="text/javascript" src="add_item.js"></script>
<link href="style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">

//HTML 5 audio play button

var playSound = {
	
	currentSound:null,
	currentSoundHTML:null,
	
	trigger: function(previewId) {
	
	
	if(this.currentSound && this.currentSoundId!=previewId){
		
	this.currentSound.pause();
	this.currentSoundHTML.innerHTML="PLAY";
	
	}
	
	newSound=document.getElementById('audiotag'+previewId);
	newSoundHTML=document.getElementById('control_btn'+previewId);
	
	if(newSoundHTML.innerHTML=="PLAY"){
    newSound.play();
	newSoundHTML.innerHTML="PAUSE";
	
	this.currentSoundId=previewId;
	this.currentSound=document.getElementById('audiotag'+previewId);
	this.currentSoundHTML=document.getElementById('control_btn'+previewId);
	
	newSound.onended = function() {
	newSound.pause();
	newSoundHTML.innerHTML="PLAY";
	};
	
	}else{
	newSound.pause();
	newSoundHTML.innerHTML="PLAY";
	}

	}
	
};

</script>
</head>
<body>
	
<div id="cart">
	
<strong>Cart</strong>

<div class="read-out">Items:  <span id="cart-size"></span></div>
<div class="read-out"><span>Total:  <?php echo $currency; ?></span><span id="cart-total"></span><a class="button cart-edit" href="cart_edit.php">edit</a><a class="button cart-checkout" id="cart-checkout" href="">checkout</a></div>

<div class="read-out" id="cart-updated"></div>

<script type="text/javascript">
var store_cart = new Inzu_cart("<?php echo $pay_url; ?>", "<?php echo $pay_callback; ?>");	
</script>

</div>

<div id="product_list">

<div class="item featured">		
<?php echo $featured; ?>
	
<div style="clear:both" >Track list</div>
        <hr/>
        <?php echo $track_list; ?>
</div>        
        	
<?php echo $more_releases; ?>
</div>

</body>	
</html>


