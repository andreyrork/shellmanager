function putPixel(X,Y)
{
	drawingArray += "<div style='position:absolute;left:"+ X +"px;top:" + Y +"px;width:2px;height:2px;background:white'></div>";

}

function refresh(){
	$.getJSON("/draw/update", '', function(obj) {
		debugger;
		$(".drawing").html(obj);
	});	
}

function paint() {
	$(".drawing").append(drawingArray);
	//$.getJSON("/draw/paint", {draw:drawingArray});
	drawingArray = '';
}

var mouseDownFlag = false;
var drawingArray ='';

$(document).ready(function(){
	$(".drawing").mousedown(function(e){
		mouseDownFlag = true;
		putPixel(e.pageX,e.pageY);
	});	
	$(this).mouseup(function(e){
		mouseDownFlag = false;
	});	
	$(".drawing").mousemove(function(e){
		if(mouseDownFlag)
		{
			putPixel(e.pageX,e.pageY);
		}
		
		
	});
	setInterval("paint()",200);
	//setInterval('refresh()', 10000);
});