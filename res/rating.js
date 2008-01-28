var minRating, maxRating;

function tx_irfaq_recordVote(selectEl, minr, maxr) {
	var value = selectEl.options[selectEl.selectedIndex].value;
	if (value != '') {
		window.open(value, 'tx_irfaq_rating', 'width=100,height=75');
	}
	minRating = minr; maxRating = maxr;
}

function tx_irfaq_setRating(uid,rating,count) {
	// Update rating
	var el = document.getElementById('tx-irfaq-rating-bar-' + uid);
	if (el && el.style) {
		var s = '' + 55*(rating-minRating)/(maxRating-minRating);
		el.style.width = '' + parseInt(s) + 'px';
	}
	// Update count
	el = document.getElementById('tx-irfaq-count-' + uid);
	if (el) {
		el.innerHTML = '<b>' + count + '</b>';
	}
	// Hide select box
	el = document.getElementById('tx-irfaq-select-' + uid);
	if (el && el.style) {
		el.style.display = 'none';
	}
	// Flash all
	el = document.getElementById('tx-irfaq-rating-' + uid);
	if (el && el.style) {
		el.style.textDecoration = 'blink';
		setTimeout('tx_irfaq_removeBlink(' + uid + ')', 4000);
	}
}

function tx_irfaq_removeBlink(uid) {
	el = document.getElementById('tx-irfaq-rating-' + uid);
	if (el && el.style) {
		el.style.textDecoration = '';
	}
}