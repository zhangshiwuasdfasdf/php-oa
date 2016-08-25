// JavaScript Document
function getNow(){
	var now=new Date();
	var year=now.getYear()+1900;
	var month=now.getMonth()+1;
	var date=now.getDate();
	var day=now.getDay();
	var arr_week=new Array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
	var week=arr_week[day];
	var hours=now.getHours();
	var minutes=now.getMinutes();
	var seconds=now.getSeconds();
	var time="当前时间："+year+"/"+month+"/"+date+' '+hours+":"+minutes;
	$("#left_span").html(time)
}
getNow();


function allowDrop(ev){  
	ev.preventDefault();  
}  
  
var srcdiv = null;  
function drag(ev,divdom){  
	srcdiv=divdom;  
	ev.dataTransfer.setData("text/html",divdom.innerHTML);  
}  
  
function drop(ev,divdom){  
	ev.preventDefault();  
	if(srcdiv != divdom){  
		srcdiv.innerHTML = divdom.innerHTML;  
		divdom.innerHTML=ev.dataTransfer.getData("text/html");  
	}  
} 


var clicked = "Nope.";
var mausx7 = "0";
var mausy7 = "0";
var winx7 = "0";
var winy7 = "0";
var difx7 = mausx7 - winx7;
var dify7 = mausy7 - winy7;
				
$(".bottom").mousemove(function (event) {
	mausx7 = event.pageX;
	mausy7 = event.pageY;
	winx7 = $(".bottom").offset().left;
	winy7 = $(".bottom").offset().top;
	if (clicked == "Nope.") {
		difx7 = mausx7 - winx7;
		dify7 = mausy7 - winy7;
	}if(winx7<0){
		$(".bottom").addClass("xuanz")
	}

	var newx7 = event.pageX - difx7 - $(".bottom").css("marginLeft").replace('px', '');
	var newy7 = event.pageY - dify7 - $(".bottom").css("marginTop").replace('px', '');
	$(".bottom").css({ top: newy7, left: newx7 });
	$(this).css("cursor","move");	
	/*var r=$(".bottom").offset().left;
	var w=$(".bottom").width();
	var h=$(".bottom").height();*/
});

$(".bottom").mousedown(function (event) {
	clicked = "Yeah.";
});

$("html").mouseup(function (event) {

	clicked = "Nope.";
});



$(".bottom_tj_span").click(function(){
	$(".bottom_ul").children("li").remove()
	
	$(".bottom_tc_bg").show();
	
	$(".bottom_span1,.bottom_span2").click(function(){
		$(".bottom_tc_bg").hide();
	})
})


function checkbox()
	{
	var str=document.getElementsByName("box");
	var objarray=str.length;
	var chestr="";
	for (var i=0;i<objarray;i++)
	{
	 if(str[i].checked == true)
	 {
	  chestr+=str[i].value+';';
	 }
	}
	if(chestr == "")
	{
	 alert("请先选择～！");
	}
	else
	{
		
		for(var j=0; j<check_num; j++){
			var q=chestr.split(';')
			$(".bottom_tj_span").before('<li class="bottom_ul_li"><a>'+q[j]+'</a></li>')
		}
		
	}
}

var check_num = 0;
function check(){ 
	if(event.srcElement.checked==true)
	check_num++;
	else
	check_num--;   
	if(check_num>8)
	{
		alert("最多只能选8个！");
		event.srcElement.checked=false;
		check_num--;
	}  
}


function im(){
	var z=$(".left_db img").height()
	
	if(z>='56'){
		$(".left_db img").css({'width':'auto','height':'48px'})	
	}else{
		$(".left_db img").css({'width':'100%','height':'auto'})	
	}
}		
im();