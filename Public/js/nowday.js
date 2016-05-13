// JavaScript Document
//日期
function getDate(){
	var now=new Date();
	var year=now.getYear()+1900;
	var month=now.getMonth()+1;
	var date=now.getDate();
	var day=now.getDay();
	var arr_week=new Array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
	var week=arr_week[day];
	var time="当前时间： "+year+"年"+month+"月"+date+"日 "+week;
	alert(time);	
}

//时间
function getTime(){
	var now=new Date();
	var hours=now.getHours();
	var minutes=now.getMinutes();
	var time="当前时间："+hours+":"+minutes;
	alert(time);
}


//整体
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
	var time="当前时间： "+year+"年"+month+"月"+date+"日 "+week +hours+":"+minutes+": "+seconds;
	alert(time);	
}

function sj(){
	now = document.getElementById('sj');
	var aa=new Date()
	now.innerHTML = ""+aa.getHours()+":"+aa.getMinutes();
	setTimeout(time,1000);
} 


function time(){
	now = document.getElementById('showtime');
	var aa=new Date()
	now.innerHTML = ""+aa.getHours()+":"+aa.getMinutes()+":"+aa.getSeconds()+"";
	setTimeout(time,1000);
} 