restwidgee = {
	data: {loaded:false},
	showMenu: function() {
		if (!restwidgee.data.loaded) {
			document.getElementById('rest-order').setAttribute('src', BASE_HTTP + '/html5/orderhtml5/web-widget.php?restaurantid='+restwidgee.data.id);
			restwidgee.data.loaded = true;
		}
		document.getElementById('rest-order').style.display = 'block';
		document.getElementById('rest-overlay').style.display = 'block';
		document.getElementById('rest-close').style.display = 'block';
		//document.getElementById('rest-order-now').style.display = 'block';
		
		return false;
	}
};
(function() {

function hide(i) { i.style.display = 'none'; }

	var createFrame = true,
		cssNode = document.createElement('link'),
		frame = document.createElement('iframe'),
		overlay = document.createElement('div'),
		close = document.createElement('div'),
		i='id',
		body = document.getElementsByTagName('body')[0],
	
	button = document.getElementById("lnk_fww_preview");
	
	restwidgee.data.id = RESTAURANT_TK;
	
		
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = BASE_HTTP + '/html5/orderhtml5/css/restwidgee.css';
	document.getElementsByTagName("head")[0].appendChild(cssNode);

	frame.setAttribute('id', 'rest-order');

	overlay.setAttribute(i, 'rest-overlay');
	//button.setAttribute(i, 'rest-order-now');
	close.setAttribute(i, 'rest-close');

	close.onclick = function() {
		hide(overlay);
		hide(frame);
		hide(close);
	}
	overlay.onclick = close.onclick;
	button.onclick = restwidgee.showMenu;
	
	body.appendChild(overlay);
	body.appendChild(close);
	body.appendChild(frame);
	
})();