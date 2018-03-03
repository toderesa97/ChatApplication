$(document).ready(function(){
	$('#show-text').scrollTop($('#show-text')[0].scrollHeight);

	$('#new-conver').click(function() {
		$('#search-user').toggle();
	});

	$('#search-user').on('input', function() {
		var xhr = new XMLHttpRequest();
		var input = document.getElementById('search-user').value;
		if (input == "") {
			$('#suggestion').addClass('display-none');
			return;
		}
		$('#suggestion').removeClass('display-none');
		xhr.open('GET', 'process.php?name='+input);
		xhr.onload = function() {
			console.log(this.responseText);
			$('#suggestion').html(this.responseText);

		}
		xhr.send();
	});

	$('#show-text').on('click', function() {
		if ($('#suggestion').hasClass('display-none')) {
			return;
		} else {
			$('#suggestion').addClass('display-none');
		}
	});


});
