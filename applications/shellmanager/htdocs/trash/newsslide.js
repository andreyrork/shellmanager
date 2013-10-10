 $(document).ready(function(){
	    	 $(".news p:not(:first)").hide();
	    	
	    	 $(".news h3").click(function(){
	    	 $(this).next("p").slideToggle("slow");
	    	 $(this).toggleClass("active");
	    	 });
	    	
	    	});