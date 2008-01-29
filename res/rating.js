function submitRating(id, pid, rating, check) {
	var el = document.getElementById('tx-ratings-display-' + id);
	el.style.visibility = 'hidden';
	var el = document.getElementById('tx-ratings-wait-' + id);
	el.style.visibility = 'visible';
	setTimeout('toggleBack(\'' + id + '\')', 2000);
}

function toggleBack(id) {
	var el = document.getElementById('tx-ratings-display-' + id);
	el.style.visibility = 'visible';
	var el = document.getElementById('tx-ratings-wait-' + id);
	el.style.visibility = 'hidden';
}