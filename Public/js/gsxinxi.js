// JavaScript Document
$(".main_tab1,.ft,.ft1,.main_tab2,.zzjg1").hide()
$(".main_tab0").show()
$(".nav_a1").click(function(){
		$(".main").show()
		$(".main_tab0,.ft1,.main_tab2,.zzjg1").hide()
		$(".main_tab1,.ft").show()
	})
$(".nav_a2").click(function(){
		$(".main").show()
		$(".main_tab0,.ft,.main_tab1,.zzjg1").hide()
		$(".main_tab2,.ft1").show()
	})
$(".nav_a3").click(function(){
		$(".main").hide()
		$(".zzjg1").show()
	})


$(".zz2,.zz4").hide()
var tu=document.getElementById("ztu1");
var tu2=document.getElementById("ztu2");
$(".zz1,#ztu1").toggle(function(){
		tu.src="image/dk.png"
		$(".zz2").slideDown()
	},function(){
		tu.src="image/mk.png"
		$(".zz2").slideUp()	
	})
	
$(".zz3,#ztu2").toggle(function(){
		tu2.src="image/dk.png"
		$(".zz4").slideDown()
	},function(){
		tu2.src="image/mk.png"
		$(".zz4").slideUp()		
	})
	

$(".zz2_2,.zz2_4").hide()
$(".zz2_1").toggle(function(){
		$(".zz2_2").slideDown()
	},function(){
		$(".zz2_2")	.slideUp()	
	})
$(".zz2_3").toggle(function(){
		$(".zz2_4").slideDown()
	},function(){
		$(".zz2_4")	.slideUp()	
	})
	
