opendining = {
	data: {},
	showMenu: function() {
		document.getElementById('odn-order').style.display = 'block';
		document.getElementById('odn-overlay').style.display = 'block';
		document.getElementById('odn-order-now').style.display = 'block';
		document.getElementById('odn-close').style.display = 'block';
		return false;
	}
};
(function() {

    function A() {
        try {
            var a = document.getElementsByTagName("script")
        } catch (p) {
            var a = []
        }
        var r, b, g = {};
        for (var n = 0, o = a.length; n < o; n++) {
            r = a[n];
            if (r.src.indexOf("/odn-uncompressed.js?") > -1) {
                b = r
            }
        }
        if (b) {
            var c = b.src.split("?").pop();
            if (c.indexOf("=") > 0) {
                var d = c.split("&"),
                    m;
                for (var l = 0; (m = d[l]); l++) {
                    var k = m.split("="),
                        t = k[0],
                        s = k[1];
                    if (t == "id") {
                        g.id = s
                    }
                    if (t == "v") {
                        g.v = s
                    }
                }
            } else {
                g.id = c
            }
        }
        return g
    }

function hide(i) { i.style.display = 'none'; }

var client = A();

if (!client.id) {
	alert('ODN Everywhere: Missing Client Identifier');
} else {
	var createFrame = true,
		cssNode = document.createElement('link'),
		frame = document.createElement('iframe'),
		overlay = document.createElement('div'),
		button = document.createElement('div'),
		close = document.createElement('div'),
		i='id',
		body = document.getElementsByTagName('body')[0],
		existingLink = document.getElementById('odn-link');
		
	opendining.data.id = client.id;
		
	if (!existingLink) {
		var link = document.createElement('a');
		link.setAttribute('id', 'odn-link');
		link.setAttribute('href', 'http://www.opendining.net');
		link.innerHTML = 'Online ordering provided by Open Dining';
		body.appendChild(link);
	}
		
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = 'http://www.opendining.net/app/extcss/'+client.id+'.css';
	document.getElementsByTagName("head")[0].appendChild(cssNode);

	frame.setAttribute('id', 'odn-order');
	/*!
	* frame.setAttribute('src', 'http://www.opendining.net/app/locations/'+client.id);
	*/
	frame.setAttribute('src', 'http://mobileappssandbox.com/anwar/menu.php');
	overlay.setAttribute(i, 'odn-overlay');
	button.setAttribute(i, 'odn-order-now');
	close.setAttribute(i, 'odn-close');

	close.onclick = function() {
		hide(overlay);
		hide(frame);
		hide(close);
	}
	overlay.onclick = close.onclick;
	button.onclick = opendining.showMenu;
	
	// Set onclick for any items with a 'show-menu' class
	if (document.getElementsByClassName) {
		var elements = document.getElementsByClassName('show-menu');
		for (var i=0; i<elements.length; i++) {
			elements[i].onclick = opendining.showMenu;
		}
	} else {
		var allHTMLTags=document.getElementsByTagName("*");
		for (var i=0; i<allHTMLTags.length; i++) {
			if (allHTMLTags[i].className.indexOf('show-menu') > -1) {
				allHTMLTags[i].onclick = opendining.showMenu;
			}
		}
	}
	body.appendChild(button);
	body.appendChild(overlay)
	body.appendChild(close);
	body.appendChild(frame);
}
})();