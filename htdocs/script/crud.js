$(document).ready(function(){
	$(".delete").click(function() {
		if (confirm("do you realy wanna delete this?")) {
			var node = $(this);
			
			function remove(json) {
			
				if (json.result == 'success') {
					node.parents("tr").remove();
				} else {
					alert(json.result);
				}
			}
			
			var href = $(this).attr("href");
			jQuery.getJSON(href, {}, remove);
		}
		
		return false;
	});

});
