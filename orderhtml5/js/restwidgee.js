function show(i) { document.getElementById(i).style.display = 'block'; }
function hide1(i) { document.getElementById(i).style.display = 'none'; }

restwidgee = {
	data: {loaded:false},
	showMenu: function() {
		if (!restwidgee.data.loaded) {
			document.getElementById('rest-order').setAttribute('src', 'http://' + restwidgee.data.domain + '/html5/orderhtml5/web-widget.php?restaurantid='+restwidgee.data.id);
			restwidgee.data.loaded = false;
		}
		document.getElementById('rest-order').style.display = 'block';
		document.getElementById('rest-overlay').style.display = 'block';
		document.getElementById('rest-order-now').style.display = 'block';
		document.getElementById('rest-close').style.display = 'block';
		return false;
	}
};

(function() {
		var DocumentURL = new Object();
		if (document.getElementsByTagName.length > -1) 
		{ DocumentURL = document.getElementsByTagName("script"); } 
		else if (document.body.all)
		{ DocumentURL = document.body.all.tags("script"); }
		var r = DocumentURL[0];
		//alert("R Value "+r.src);
		var reg_ex = /^((http[s]?|ftp):\/)?\/?([^:\/\s]+)((\/\w+)*\/)([\w\-\.]+[^#?\s]+)(.*)?(#[\w\-]+)?$/;
		var domain_name = r.src.match(reg_ex);
		var Tokenstring1 = new Array();
		Tokenstring1 = r.src.split('=');
		domain_name = domain_name[3];
		var Tokenstring = Tokenstring1[1];
		//alert("Domain "+domain_name+" Token "+Tokenstring);
		
	
function hide(i) { i.style.display = 'none'; }


if (Tokenstring=="") {
	alert('Missing Token');
} else {
	var createFrame = true,
		cssNode = document.createElement('link'),
		frame = document.createElement('iframe'),
		overlay = document.createElement('div'),
		button = document.createElement('div'),
		close = document.createElement('div'),
		i='id',
		body = document.getElementsByTagName('body')[0],
		existingLink = document.getElementById('rest-link');
	restwidgee.data.id = Tokenstring;
	restwidgee.data.domain = domain_name;
	if (!existingLink) {
		var link = document.createElement('a');
		link.setAttribute('id', 'rest-link');
		link.setAttribute('href', restwidgee.data.domain);
		link.innerHTML = 'Order now!';
		body.appendChild(link);
	}
		
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = 'http://'+restwidgee.data.domain+'/html5/orderhtml5/css/restwidgee.css';
	document.getElementsByTagName("head")[0].appendChild(cssNode);

	frame.setAttribute('id', 'rest-order');

	overlay.setAttribute(i, 'rest-overlay');
	button.setAttribute(i, 'rest-order-now');
	close.setAttribute(i, 'rest-close');

	close.onclick = function() {
		hide(overlay);
		hide(frame);
		hide(close);
	}
	overlay.onclick = close.onclick;
	button.onclick = restwidgee.showMenu;
	
	body.appendChild(button);
	body.appendChild(overlay)
	body.appendChild(close);
	body.appendChild(frame);
}
})();