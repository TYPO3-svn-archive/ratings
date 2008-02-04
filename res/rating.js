function tx_ratings_submit(id, rating, check) {
	$('tx-ratings-display-' + id).style.visibility = 'hidden';
	$('tx-ratings-wait-' + id).style.visibility = 'visible';
	new Ajax.Updater('tx-ratings-' + id, 'index.php?eID=tx_ratings_ajax', {
		asynchronous: true,
		method: 'post',
		parameters: 'ref=' + id + '&rating=' + rating + '&data=' + tx_ratings_ajaxData + '&check=' + check
	});
}
