/*
	Inzu Cart
*/

function Inzu_cart(checkoutlink,callback) {

	this.helper = new Inzu_cartHelper();
	
	this.checkoutlink = checkoutlink;
	this.callback = callback;
	this.timer = false;
	
	//Get 'cart' cookie or if undefined create it
	this.cookie = this.helper.getCookie('cart');

	if ( typeof undefined === typeof this.cookie || !this.cookie ) {
		
		this.helper.setCookie('cart', JSON.stringify({ 'cart' : [], 'cart_total' : '0.00', 'cart_size' : '0' }), 7);
		this.cookie = this.helper.getCookie('cart');
			
	}
	
	
	this.refreshDisplay(this.cookie.cart);

}

//Display the cart contents and form checkout URL using the cookie data
Inzu_cart.prototype.refreshDisplay = function(cart){	

	//Get cart data
    this.helper.loopCart(cart);

	//Display data
	document.getElementById("cart-size").innerHTML = this.helper.cart_data.size;
	document.getElementById("cart-total").innerHTML = this.helper.cart_data.price.toFixed(2); //2 decimal places

	//Form checkout URL
	var checkout = this.checkoutlink + "?item_code" + this.helper.cart_data.items + "&callback=" + this.callback;	
	document.getElementById("cart-checkout").setAttribute("href", checkout);
	
}



Inzu_cart.prototype.addItem = function(el){

	var data = null, code, price;
	
	//Get information for selected item
	
	if ( typeof undefined !== typeof el ) {	 
		
		//Get product price and item code	
		var inputs = el.parentElement.getElementsByTagName("input");
		
		if(inputs.length > 0){	
		
			for (var i = 0; i < inputs.length; i++) {
				
				if ( 'item_code' === inputs[i].name ) code = inputs[i].value;
				if ( 'price' === inputs[i].name ) price =  inputs[i].value;
				
			}	
			
		} else {		
		
		//Get for variations get selected variations price and item code		
		var selected = el.parentElement.getElementsByTagName("select");
	
			for ( var i=0; i < selected.length; i++) {
			
				var values = selected[i].options[selected[i].selectedIndex].value;				
				values = values.split(",");
	
				code = values[0];
				price = values[1];
			}
		
		}

		// create a data object of the items core details.
		data = {
			'item_code' : code,
			'price' : price
		};


		
	//Add data to cookie

	if ( typeof undefined !== typeof data.item_code ) {
		
		var increment = false;
		
		//Search cart for a product that matches the one being added
		for ( var key in this.cookie.cart ) {
			
			if ( this.cookie.cart[key].item_code === data.item_code ) {
				this.cookie.cart[key].quantity++;
				increment = true;
			}
			
		}

		//If we aren't incrementing or the cart is empty set the quantity to 1 and add to the cart
		if ( !increment || this.cookie.cart.length === 0) {
			
			data.quantity = 1;
			this.cookie.cart.push(data);
			
		}

		//Refresh cart display
		this.refreshDisplay(this.cookie.cart);

		//Overwrite the cookie with the new cart data
		this.helper.setCookie('cart', JSON.stringify(this.cookie), 7);

		//Display 'updated' message
		this.cartUpdated();	
	}
	
	}

}

//Callback function for when the cart is updated
Inzu_cart.prototype.cartUpdated = function(){

	var fadeDelay = 1500; //How long the message is displayed
	
	//Reset and update the message display
	var outputElement = document.getElementById("cart-updated");
	outputElement.innerHTML = 'Item Added';
	outputElement.style.opacity = 1;
	outputElement.style.filter = 'alpha(opacity=100)';
	
	//Fade update message
	clearInterval( this.timer );
	this.helper.fadeIn(this, document.getElementById("cart-updated"), fadeDelay);
	
}



/*
	Inzu Cart Edit 
*/

function Inzu_cartEdit(pay_url){

	this.helper = new Inzu_cartHelper();
	
	this.checkoutlink = pay_url;
	this.timer = false;
	this.cookie = this.helper.getCookie('cart');
	this.total = document.querySelector('#cart_total');

}


Inzu_cartEdit.prototype.adjust = function(i, value) {
	
	//If the input has been erased do nothing
	if (value === '' ) return;

	//If input is zero remove item
	if ( value === '0' ) {
		
	this.deleteItem(i);
		
	} else {

	//Adjust quantity of item
	this.cookie.cart[parseInt(i)].quantity = parseInt(value);
	
	//Get cookie data
	this.helper.loopCart(this.cookie.cart);
	
	//Update total
	this.total.innerHTML = this.helper.cart_data.price.toFixed(2);
	
	//Update checkout link
	var checkout = this.helper.cart_data.items;	
	document.getElementById("checkout_link").setAttribute("href", checkout);

	//Set cookie
	this.helper.setCookie('cart', JSON.stringify(this.cookie), 7);
	
	this.quantityUpdated(); //Callback
	
	}
	
}


//Callback function for when quantity is changed
Inzu_cartEdit.prototype.quantityUpdated = function() {

	var fadeDelay = 1500; //How long the message is displayed
	
	//Reset and update the message display
	var outputElement = document.getElementById("quantity-updated");
	outputElement.innerHTML = 'Cart edited';
	outputElement.style.opacity = 1;
	outputElement.style.filter = 'alpha(opacity=100)';
	
	//Fade update message
	clearInterval( this.timer );
	this.helper.fadeIn(this, document.getElementById("quantity-updated"), fadeDelay);
	
}


Inzu_cartEdit.prototype.deleteItem = function(item_code,i){
	
	for ( var key in this.cookie.cart ) {
		
		if ( this.cookie.cart[key].item_code === item_code.toString() ) {
			//Adjust cookie
			this.cookie.cart.splice(key, 1);
			this.helper.setCookie('cart', JSON.stringify(this.cookie), 7);
		}
		
	}
	

	//Delete and fade out row
	this.helper.fadeOut(this, document.getElementById('form_main_'+i));	  

	//Update display
	this.helper.loopCart(this.cookie.cart);
	this.total.innerHTML = this.helper.cart_data.price.toFixed(2);
	
	//Update checkout link
	var checkout = this.helper.cart_data.items;	
	document.getElementById("checkout_link").setAttribute("href", checkout);

	
}



/*
	Inzu helper object.	
*/


function Inzu_cartHelper(){}

//Loop over items in cart cookie to get total price, cart size and a format URL data for checkout
Inzu_cartHelper.prototype.loopCart = function(cart){
	
	var size = 0, price = 0, items = '';

	for( var key in cart ){
		
		price += cart[key].price * cart[key].quantity;
		size += cart[key].quantity;
		
			for( var i = 0; i < cart[key].quantity; i++ ){
				items += '=' + cart[key].item_code;
			}
	
	}
	
	this.cart_data = { 'price' : Math.round(price * 100) / 100, 'size' : size, 'items' : items};
	
}




//Fade in selected element
Inzu_cartHelper.prototype.fadeIn = function(obj, element, fadeDelay){
	
    var op = 0;
    element.style.display = 'inline-block';
	element.style.opacity = 0;
    element.style.filter = 'alpha(opacity="0")';
	
    obj.timer = setInterval(function () {
	    
        if ( op >= 0.9 ) {
	        
            clearInterval(obj.timer);
            setTimeout( function() {obj.helper.fadeOut(obj, element)}, fadeDelay );
			
        }
        
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op += 0.1;
        
    }, 50);
    
}


//Fade out selected element. If an element is passed as the 'remove' parameter it will be removed
Inzu_cartHelper.prototype.fadeOut = function(obj, element, remove){
    var op = 1;
    obj.timer = setInterval(function () {
	    
        if ( op <= 0.1 ){
	        
            clearInterval( obj.timer );
            element.style.display = 'none';
            
            if ( typeof undefined !== typeof remove ) remove.parentNode.removeChild(ele);
            
        }
        
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op -= 0.1;

    }, 50);    
}



//Get the cookie's JSON data
Inzu_cartHelper.prototype.getCookie = function(name) {
	
 var result = document.cookie.match(new RegExp(name + '=([^;]+)'));
 result && (result = JSON.parse(result[1]));
 
 return result;
 
}


//Set a cookie with a name, value and expiration date
Inzu_cartHelper.prototype.setCookie = function(cname, cvalue, exdays) {
	
    var d = new Date();
    d.setTime( d.getTime() + ( exdays*24*60*60*1000 ) );
    
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
    
}