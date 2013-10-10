/*
 * Скрипт для изменения отображенного календаря при смене месяца или года
 */
function Sucess(obj)
	{
		debugger;
		//$(".calendarblock").replaceWith("<div class='calendarblock'>" + obj + "</div>")
	}

$(document).ready(function(){
	$("select").change(function(){
		var selMonth = $("select[name='monthselect'] option:selected").text();
		var selYear = $("select[name='yearselect'] option:selected").text();
		$.getJSON("/calendar/ajax", {month: selMonth, year: selYear}, function(obj) {
			$(".calendarblock").html(obj);
			});
		//debugger;
	});
});