function tx_ratings_submit(id, rating, ajaxData, check) {
	$('#tx-ratings-display-' + id).css('visibility', 'hidden');
	$('#tx-ratings-wait-' + id).css('visibility', 'visible');
	$.ajax({
		type: 'POST',
		url: 'index.php?eID=tx_ratings_ajax',
		async: true,
		data: 'ref=' + id + '&rating=' + rating + '&data=' + ajaxData + '&check=' + check,
		success: function(html){
			$('#tx-ratings-' + id).html(html);
		}
	});
}
