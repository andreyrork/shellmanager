jQuery.fn.check = function(mode) {
   var mode = mode || 'on';
   
   return this.each(function() 
   {
     switch(mode) {
       case 'on':
         this.checked = true;
         break;
       case 'off':
         this.checked = false;
         break;
       case 'toggle':
         this.checked = !this.checked;
         break;
     }
   });
 };

$(document).ready(function() {

	// check all checkboxes
	$(".check_all").click(function() {
		$(":checkbox").check('toggle');
	});
	
	// check all checkboxes
	$(".uncheck_all").click(function() {
		$(":checkbox").check('off');
	});
	
	// check processed checkboxes
	$(".check_processed").click(function() {
		$(".processed :checkbox").check('toggle');
	});
	
	// check not_processed checkboxes
	$(".check_not_processed").click(function() {
		$(".not_processed :checkbox").check('toggle');
	});
	
	// check success processed checkboxes
	$(".check_success").click(function() {
		$(".success :checkbox").check('toggle');
	});
	
	// check failure processed checkboxes
	$(".check_not_success").click(function() {
		$(".failure :checkbox").check('toggle');
	});

});
