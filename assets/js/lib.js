$(document).ready(function(){
	$('#show-text').scrollTop($('#show-text')[0].scrollHeight);

	$('#new-conver').click(function() {
		$('#search-user').toggle(10000);
	});

	$('#search-user').on('input', function() {
		var xhr = new XMLHttpRequest();
		var input = document.getElementById('search-user').value;
		if (input == "") {
			return;
		}
		xhr.open('GET', 'process.php?name='+input);
		xhr.onload = function() {
			console.log(this.responseText);
		}
		xhr.send();
	});
});
