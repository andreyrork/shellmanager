$(document).ready(function(){

 $(".menu a").append("<em></em>");

 $(".menu a").hover(function() {
 $(this).find("em").animate({opacity: "show", top: "-75"}, "slow");
 var hoverText = $(this).attr("title");
 $(this).find("em").text(hoverText);
 }, function() {
 $(this).find("em").animate({opacity: "hide", top: "-85"}, "fast");
 });


});