var X1 = null;
var Y1 = null;
var offset = null;
var offsetX = null;
var offsetY = null;
$(document).ready(function(){
	$(".rect").mousedown(function(e){
		offset = $(this).offset();

		offsetX = offset.left - e.pageX;
		offsetY = offset.top - e.pageY;
//		alert(offsetX + " " + offsetY);		
		$(this).parent().addClass("selected");		

	}); 
	$(window).mouseup(function(){
		$(".selected").removeClass("selected");		
		
	}); 
	$(window).mousemove(function(e){
		//alert(e.PageX + " " + e.PageY);
		if ($(".rect").parent().is(".selected")) {
			X1 = e.pageX + offsetX + "px";
			Y1 = e.pageY + offsetY + "px";
			$('.rect').css({top:Y1,left:X1});	
		}
	}); 	
	
});