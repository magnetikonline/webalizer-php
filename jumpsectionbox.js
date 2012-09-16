(function() {

	var framecount = 12,
		jumpsectionel,
		ismoving = 0,
		curframe = 0,
		curwidth = 0,
		interval,
		targetwidth;

	function easein(c,t,s,d) {

		// c = current frame, t = total frames, s = start, d = delta
		return (d * (c /= t) * c) + s;
	}

	function setboxwidth(size) {

		// +5 since we don't want the <ul> width ever to be full zero
		// this allows IE7 & Safari to correctly hide all the links - if the width is zero is goes a bit weird
		jumpsectionel.style.width = (size >= 0) ? (size + 5) + 'px' : 'auto';
	}

	function animateend() {

		if (!interval) return;
		window.clearInterval(interval);
		interval = false;
	}

	function animatestart(v) {

		// hide open side box icon
		jumpsectionel.className = 'open';

		// work out final natural width of box
		setboxwidth(-1);

		// minus 26 to compensate for the 24px of padding and 2px of border
		targetwidth = jumpsectionel.offsetWidth - 26;
		setboxwidth(curwidth);

		// setup interval timer and ismoving direction
		animateend();
		interval = window.setInterval(animatebox,20);

		ismoving = v;
	}

	function animatebox() {

		curframe += ismoving;
		curwidth = easein(curframe,framecount,0,targetwidth);
		setboxwidth(curwidth);

		if (curframe >= framecount) {
			// end animation - opened
			curframe = framecount;
			animateend();
			setboxwidth(-1);
			ismoving = 0;
		}

		if (curframe <= 0) {
			// end animation - closed
			curframe = 0;
			animateend();
			setboxwidth(0);
			ismoving = 0;

			// make open icon visible
			jumpsectionel.className = '';
		}
	}

	function ismouseenterleave(el,e) {

		function ischildof(parent,child) {

			if (!child) return false;
			if (parent == child) return true;

			// call ischildof() recursively
			return ischildof(parent,child.parentNode);
		}

		var rel = e.relatedTarget || ((e.type == 'mouseover') ? e.fromElement : ((e.type == 'mouseout') ? e.toElement : false));
		return !(!rel || ischildof(el,rel));
	}

	function createmonthlyjumptonode() {

		var linode = document.createElement('li'),
			anode = document.createElement('a');

		anode.appendChild(document.createTextNode('Monthly Statistics'));
		anode.href = '#content';
		linode.appendChild(anode);

		return linode;
	}

	function init() {

		var tablefound = document.getElementsByTagName('table');
		if (!tablefound.length) {
			// reset timeout to try again later
			window.setTimeout(init,20);
			return;
		}

		// first <table> exists, so we know the jump section box will now be complete in the dom
		jumpsectionel = document.getElementById('jumpsection');

		// insert new 'monthly statistics' link at top of jump section set
		jumpsectionel.insertBefore(createmonthlyjumptonode(),jumpsectionel.firstChild);

		// attach event handlers to show/hide side box
		jumpsectionel.onmouseover = function(e) {

			if (!ismouseenterleave(jumpsectionel,e || window.event)) return;
			animatestart(1);
		};

		jumpsectionel.onmouseout = function(e) {

			if (!ismouseenterleave(jumpsectionel,e || window.event)) return;
			animatestart(-1);
		};
	}

	function ie6orlower() {

		var match;
		if (match = /MSIE ([0-9]+)\./.exec(navigator.appVersion)) {
			if (match[1] <= 6) return true;
		}

		return false;
	}

	// test for IE6 or lower, if found don't install jump section box system
	if (!ie6orlower()) {
		init();

		// add class to <html> tag to hide default HTML jump section box right away
		document.getElementsByTagName('html')[0].className = 'jsenabled';
	}
})();
