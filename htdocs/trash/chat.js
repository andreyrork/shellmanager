function send(CurMessage, CurAuthor){
	$.getJSON("/chat/message", {message: CurMessage, author: CurAuthor}, function(obj) {
		$(".messagewindow").html(obj);
	});	
}

$(document).ready(function(){
	$("input[name='sendmessage']").click(function(){
		var CurMessage = $("input[name='message']").val();
		var CurAuthor = $("input[name='author']").val();
		
		send(CurMessage, CurAuthor);
		
	});

	setInterval("send('', '')", 5000);
});