// JavaScript Document
//当前时间
function dqsj(){
	var now=new Date();
	var year=now.getYear()+1900;
	var month=now.getMonth()+1;
	var date=now.getDate();
	var day=now.getDay();
	var arr_week=new Array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
	var week=arr_week[day];
	var time=year+"年"+month+"月"+date+"日 "+week;
	$(".tqd1s2").text(time)
}
setInterval(dqsj,500);

$(".ggs1").hide();
$(".gks1").hide();
$(".gzs1").hide();
if($(".ggul li").length>7){
	$(".ggs1").show()
}else{
	$(".ggs1").hide()
	}
	
if($(".gkul li").length>7){
	$(".gks1").show()
}else{
	$(".gks1").hide()
	}

if($(".gzul li").length>7){
	$(".gzs1").show()
}else{
	$(".gzs1").hide()
	}
	
//金华
var jimg=$(".jhma1 img").height()*0.5
function j(){
$(".jhm1").css('height',jimg)
$(".jhm2").css('height',jimg)
}
setInterval(j,500);
