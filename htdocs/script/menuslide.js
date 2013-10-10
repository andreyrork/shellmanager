	var menu = null;
$(document).ready(function(){
	$(".btn-slide").toggle(function(){
		$(".panel").animate({"width":"+=140px"},"slow");
		return false;
	}, function(){
		$(".panel").css("display","none");
		$(".panel").css("width","0px");		
//		$(".panel").animate({"width":"-=80px"},"slow");
		return false;
	});
	
	menu = 0;

	 $(window).scroll(function () {
		 offset = menu + $(document).scrollTop() + "px";
		 $('.left').animate({top:offset},{duration:1000,queue:false});
	});
});