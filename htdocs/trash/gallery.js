$(document).ready(function (){
	$(".thumbs a").click(function() {
		var href = $(this).attr("href");
		$(".image img").attr("src", href);
		return false;
	});




});
