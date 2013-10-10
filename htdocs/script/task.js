$(document).ready(function() {
	$(".runSubmit").click(onRunSubmit);
    
    $(".run").click(onRunClick);
    
    $("select").change(function(){
		var action = $("select option:selected").attr("value");
		$("tr").each(function(){
			var id = $(this).attr("id");
			if (id == '') return;
			
			if ($(this).hasClass(action)) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});
    
});


function removeTr() {

}

function processTr() {

}

function onRunSubmit() {
	$(":checkbox").each(function(){
	if(this.checked == false) {
			var parent = $(this).parent().parent();
		 	parent.remove();
		 	
		}
	});
		
	$("tr").each(function(){
		var tr = this;
		var id = $(this).attr("id");
		
		var basePath = $("#basePath").attr('class');
		
		if (id == '') return;
		$.getJSON(basePath + "/task/run/id/"+id, '', function(response){
			$(tr).attr("class",response.class);
			var msg = "<li>" + response.error + "</li>";
			
			if (response.error) {
				$("ul.error").append(msg);
			}
		
		});
	});
	
	return false;
}


function onRunClick() {
	var href = $(this).attr('href');
	var id = $(this).attr('id');
	
	$.getJSON(href, '', function(response){
		$("tr#" + id).attr("class", response.class);
		var msg = "<li>" + response.error + "</li>";
		
		if (response.error) {
			$("ul.error").append(msg);
		}
	
	});
		
	return false;
}

