function Inzu_cart(checkoutlink,callback) {
	
	this.checkoutlink=checkoutlink;
	this.callback=callback;
	
	this.updateCart();

}	

Inzu_cart.prototype.updateCart = function(el){
	
	if(typeof(el)!="undefined"){

		var data=""; 
	
		var inputs = el.parentElement.getElementsByTagName("input");
		
		//Get product price and item code
		
		if(inputs.length > 0){
		
			for (var i = 0; i < inputs.length; i++) {
		    	data += "&" + inputs[i].name + "=" + inputs[i].value;
			}
			
			data = data.slice(1);
			
		}else{
			
		//Get for variations get selected variation
		
		var selected = el.parentElement.getElementsByTagName("select");
		
			for (var i = 0; i < selected.length; i++) {
			
				var values=selected[i].options[selected[i].selectedIndex].value;
				
				values = values.split(",");
					
				data += "item_code=" + values[0] + "&price=" + values[1];
				
			}	

		}
	}
	
	var obj=this;
	
	this.AJAX("cart_data.php", data,  obj);
	
}




Inzu_cart.prototype.cartUpdated = function(serverResponse){
	

	
	//callback function when the cart is updated
	
	//convert the json response to an object
	var response = eval('(' + serverResponse + ')');

	document.getElementById("cart-size").innerHTML=response.cart_size;
	document.getElementById("cart-total").innerHTML=response.cart_total;
	
	//Update checkout link
	var checkout = this.checkoutlink + "?item_code" + response.cart_items + "&callback=" + this.callback;

	
	document.getElementById("cart-checkout").setAttribute("href", checkout);
	
	if(response.msg == "Item added"){
	var fadeDelay = 1500; //How long the message is displayed
	
	//reset and update the message display
	var outputElement = document.getElementById("cart-updated");
	outputElement.innerHTML = response.msg;
    outputElement.style.display = 'block';
	outputElement.style.opacity = 1;
	outputElement.style.filter = 'alpha(opacity=100)';	
	
	if(response.success==true){
		fadeIn(document.getElementById("cart-updated"), fadeDelay);
	}
	}
}



Inzu_cart.prototype.AJAX = function(url, dataString,  obj){

    obj.xmlhttp = new XMLHttpRequest();
    obj.xmlhttp.onreadystatechange = function(){
        if (obj.xmlhttp.readyState == 4 && obj.xmlhttp.status == 200){
            obj.cartUpdated(obj.xmlhttp.responseText);
        }
    }
    obj.xmlhttp.open("POST", url, true);
	obj.xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	obj.xmlhttp.send(dataString);
}


function fadeIn(element, fadeDelay){
    var op = 0; 
    element.style.display = 'block';
	
	element.style.opacity = 0;
    element.style.filter = 'alpha(opacity="0")';
	
    var timer = setInterval(function () {
        if (op >= 0.9){
            clearInterval(timer);
			setTimeout(function(){fadeOut(element);},fadeDelay);
        }
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op += 0.1;
    }, 50);
}


function fadeOut(element){
    var op = 1;
    var timer = setInterval(function () {
        if (op <= 0.1){
            clearInterval(timer);
            element.style.display = 'none';
        }
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op -= 0.1;
    }, 50);
}
