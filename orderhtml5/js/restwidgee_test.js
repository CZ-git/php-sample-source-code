var scripts = document.getElementsByTagName('script');
var lastScript = scripts[scripts.length - 1];
alert(lastScript.src);

restwidgee = {
	data: {loaded:false},
	showMenu: function() {
		if (!restwidgee.data.loaded) {
			document.getElementById('rest-order').setAttribute('src', 'http://appsomen.com/html5/orderhtml5/web-widget.php?restaurantid='+restwidgee.data.id);
			restwidgee.data.loaded = true;
		}
		document.getElementById('rest-order').style.display = 'block';
		document.getElementById('rest-overlay').style.display = 'block';
		document.getElementById('rest-order-now').style.display = 'block';
		document.getElementById('rest-close').style.display = 'block';
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
            if (r.src.indexOf("/restwidgee.js?") > -1) {
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
	alert('Missing Restaurant Id');
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
		
	restwidgee.data.id = client.id;
		
	if (!existingLink) {
		var link = document.createElement('a');
		link.setAttribute('id', 'rest-link');
		link.setAttribute('href', 'http://appsomen.com');
		link.innerHTML = 'Order now!';
		body.appendChild(link);
	}
		
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = 'http://appsomen.com/html5/orderhtml5/css/restwidgee.css';
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