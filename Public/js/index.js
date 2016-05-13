// JavaScript Document
//工作任务
function up(){
	$(".ma2d1-ul1").animate({marginTop:-28},4000,
	function(){
		$(".ma2d1-ul1").css({marginTop:0})
		$(".ma2d1-ul1 li:first").insertAfter($(".ma2d1-ul1 li:last"))	
	})	
}
setInterval(up,2000);

//工作报表
function u(){
	$(".ma3d1-ul1").animate({marginTop:-28},4000,
	function(){
		$(".ma3d1-ul1").css({marginTop:0})
		$(".ma3d1-ul1 li:first").insertAfter($(".ma3d1-ul1 li:last"))	
	})	
}
setInterval(u,2000);

//工作邮箱
function p(){
	$(".ma5d1-ul1").animate({marginTop:-28},4000,
	function(){
		$(".ma5d1-ul1").css({marginTop:0})
		$(".ma5d1-ul1 li:first").insertAfter($(".ma5d1-ul1 li:last"))	
	})	
}
setInterval(p,2000);

//企业概况
function o(){
	$(".ma6d1-ul1").animate({marginTop:-30},4000,
	function(){
		$(".ma6d1-ul1").css({marginTop:0})
		$(".ma6d1-ul1 li:first").insertAfter($(".ma6d1-ul1 li:last"))	
	})	
}
setInterval(o,2000);

//企业新闻
function i(){
	$(".ma7d1-ul1").animate({marginTop:-45},4000,
	function(){
		$(".ma7d1-ul1").css({marginTop:0})
		$(".ma7d1-ul1 li:first").insertAfter($(".ma7d1-ul1 li:last"))	
	})	
}
setInterval(i,2000);

//企业公告
function y(){
	$(".ma8d1-ul1").animate({marginTop:-30},4000,
	function(){
		$(".ma8d1-ul1").css({marginTop:0})
		$(".ma8d1-ul1 li:first").insertAfter($(".ma8d1-ul1 li:last"))	
	})	
}
setInterval(y,2000);

//温暖关怀
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
setInterval(t,2000);

//学习天地
function tp(){
	$(".ma11-ul1").animate({marginLeft:"-r"},2000,
	function(){
		$(".ma11-ul1").css({marginLeft:0})
		$(".ma11-ul1 li:first").insertAfter($(".ma11-ul1 li:last"))
		}
	)	
}
setInterval(tp,2000);

//生活指南
function ml(){
	$(".ma12-ul1").animate({marginLeft:"-r"},2000,
	function(){
		$(".ma12-ul1").css({marginLeft:0})
		$(".ma12-ul1 li:first").insertAfter($(".ma12-ul1 li:last"))
		}
	)	
}
setInterval(ml,2000);
