// JavaScript Document

//工作任务
if($(".ma2d1-ul1 li").length>10){
	function up(){
	$(".ma2d1-ul1").animate({marginTop:-28},4000,
	function(){
		$(".ma2d1-ul1").css({marginTop:0})
		$(".ma2d1-ul1 li:first").insertAfter($(".ma2d1-ul1 li:last"))	
	})	
}
	var xup=setInterval(up,2000);
	$(".main2").hover(function(){
		clearInterval(xup)
	},function(){
		xup=setInterval(up,2000);
	})
}else{clearInterval(xup)}

//工作报表
if($(".ma3d1-ul1 li").length>10){
function u(){
	$(".ma3d1-ul1").animate({marginTop:-28},4000,
	function(){
		$(".ma3d1-ul1").css({marginTop:0})
		$(".ma3d1-ul1 li:first").insertAfter($(".ma3d1-ul1 li:last"))	
	})	
}
var xu=setInterval(u,2000);
$(".main3").hover(function(){
		clearInterval(xu)
	},function(){
		xu=setInterval(u,2000);
	})
}else{clearInterval(xu)}

//工作邮箱
if($(".ma5d1-ul1 li").length>6){
function p(){
	$(".ma5d1-ul1").animate({marginTop:-28},4000,
	function(){
		$(".ma5d1-ul1").css({marginTop:0})
		$(".ma5d1-ul1 li:first").insertAfter($(".ma5d1-ul1 li:last"))	
	})	
}
var xp=setInterval(p,2000);
$(".main5").hover(function(){
		clearInterval(xp)
	},function(){
		xp=setInterval(p,2000);
	})
	}else{clearInterval(xp)}
	
//企业概况
if($(".ma6d1-ul1 li").length>4){
function o(){
	$(".ma6d1-ul1").animate({marginTop:-30},4000,
	function(){
		$(".ma6d1-ul1").css({marginTop:0})
		$(".ma6d1-ul1 li:first").insertAfter($(".ma6d1-ul1 li:last"))	
	})	
}
var xo=setInterval(o,2000);
$(".main6").hover(function(){
		clearInterval(xo)
	},function(){
		xo=setInterval(o,2000);
	})
	}else{clearInterval(xo)}
	
//企业新闻
if($(".ma7d1-ul1 li").length>3){
function i(){
	$(".ma7d1-ul1").animate({marginTop:-45},4000,
	function(){
		$(".ma7d1-ul1").css({marginTop:0})
		$(".ma7d1-ul1 li:first").insertAfter($(".ma7d1-ul1 li:last"))	
	})	
}
var xi=setInterval(i,2000);
$(".main7").hover(function(){
		clearInterval(xi)
	},function(){
		xi=setInterval(i,2000);
	})
}else{clearInterval(xi)}

//企业公告
if($(".ma8d1-ul1 li").length>3){
function y(){
	$(".ma8d1-ul1").animate({marginTop:-30},4000,
	function(){
		$(".ma8d1-ul1").css({marginTop:0})
		$(".ma8d1-ul1 li:first").insertAfter($(".ma8d1-ul1 li:last"))	
	})	
}
 var xy=setInterval(y,2000);
 $(".main8").hover(function(){
		clearInterval(xy)
	},function(){
		xy=setInterval(y,2000);
	})
}else{clearInterval(xy)}
 

//温暖关怀
if($(".ma10-ul1 li").length>2){
var r
r=$(".ma10-d2").width()*0.5
function t(){
	$(".ma10-ul1").animate({marginLeft:"-r"},2000,
	function(){
		$(".ma10-ul1").css({marginLeft:0})
		$(".ma10-ul1 li:first").insertAfter($(".ma10-ul1 li:last"))
		}
	)	
}
var xt=setInterval(t,2000);
$(".main10").hover(function(){
		clearInterval(xt)
	},function(){
		xt=setInterval(t,2000);
	})
}else{clearInterval(xt)}

//学习天地
if($(".ma11-ul1 li").length>2){
function tp(){
	$(".ma11-ul1").animate({marginLeft:"-r"},2000,
	function(){
		$(".ma11-ul1").css({marginLeft:0})
		$(".ma11-ul1 li:first").insertAfter($(".ma11-ul1 li:last"))
		}
	)	
}
var xtp=setInterval(tp,2000);
$(".main11").hover(function(){
		clearInterval(xtp)
	},function(){
		xtp=setInterval(tp,2000);
	})
}else{clearInterval(xtp)}

//生活指南
if($(".ma12-ul1 li").length>2){
function ml(){
	$(".ma12-ul1").animate({marginLeft:"-r"},2000,
	function(){
		$(".ma12-ul1").css({marginLeft:0})
		$(".ma12-ul1 li:first").insertAfter($(".ma12-ul1 li:last"))
		}
	)	
}
var xml=setInterval(ml,2000);
$(".main12").hover(function(){
		clearInterval(xml)
	},function(){
		xml=setInterval(ml,2000);
	})
}else{clearInterval(xml)}