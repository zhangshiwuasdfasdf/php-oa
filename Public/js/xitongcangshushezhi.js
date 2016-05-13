// JavaScript Document
$(".main_d1 a").removeClass("bian_a")
$(".main_d1 a:eq(1)").addClass("bian_a")
$(".main_d1 a").click(function(){
	$(".main_d1 a").index(this)
	$(".main_d1 a").removeClass("bian_a")
	$(this).addClass("bian_a")	
})

$(".xz1").hide()
$(".xinzeng").click(function(){
	$(".xz1").slideDown()
})
$(".guan").click(function(){
	$(".xz1").slideUp()	
})