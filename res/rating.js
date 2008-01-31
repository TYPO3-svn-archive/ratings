function submitRating(id, rating, check) {
	var el = document.getElementById('tx-ratings-display-' + id);
	el.style.visibility = 'hidden';
	var el = document.getElementById('tx-ratings-wait-' + id);
	el.style.visibility = 'visible';
	//setTimeout('toggleBack(\'' + id + '\')', 2000);
	new Ajax.Updater('tx-ratings-' + id, 'index.php?eID=tx_ratings_ajax', {
		asynchronous: true,
		method: 'post',
		parameters: 'ref=' + id + '&rating=' + rating + '&data=' + tx_ratings_ajaxData + '&check=' + check
	});
}

function toggleBack(id) {
	var el = document.getElementById('tx-ratings-display-' + id);
	el.style.visibility = 'visible';
	var el = document.getElementById('tx-ratings-wait-' + id);
	el.style.visibility = 'hidden';
}