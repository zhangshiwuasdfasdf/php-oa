// JavaScript Document
$.fn.toggle = function( fn, fn2 ) {
    var args = arguments,guid = fn.guid || $.guid++,i=0,
    toggle = function( event ) {
      var lastToggle = ( $._data( this, "lastToggle" + fn.guid ) || 0 ) % i;
      $._data( this, "lastToggle" + fn.guid, lastToggle + 1 );
      event.preventDefault();
      return args[ lastToggle ].apply( this, arguments ) || false;
    };
    toggle.guid = guid;
    while ( i < args.length ) {
      args[ i++ ].guid = guid;
    }
    return this.click( toggle );
  };
function hel(){
	
	//left的高度
	var h0=$(window).height()
	var h=h0-160
	$(".left").css('height',h)
	var h3=h-40//上下各20边距
	if($(".menu_ul6,.menu_ul4,.menu_ul14").height()>=h){
		$(".menu_ul6,.menu_ul4,.menu_ul14").css({'height':h3,'top':180,'width':180})
		$(".menu_ul14").css('width',180)
		$('.menu_ul14').css('height',h3)
	}
	var wh=$(".menu_ul6").width()+120
	$(".menu_ul7_0,.menu_ul7,.menu_ul7_3").css('left',wh)
	var h4=$('.menu_ul14').height()+40
	var h5=$('.menu_ul14').width()+120
	$('.menu_ul15').css('left',h5)
		
}
hel();
//菜单栏
$("#left_a1").toggle(function(){
	$("#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$("#left_a1").toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul6,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	var t=$(this).offset().top+12
	var w=$(".left").width()
	$(".ani_ss").css({"top":t,"left":w})
	$(".ani_ss").animate({opacity:1,width:'210px'})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".ani_ss").css({"top":j1,"left":w})
		
	})
},function(){
	$(".ani_ss").animate({opacity:0,width:'0px'})
	}
)

$("#left_a2").click(function(){
	$("#left_a1,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$("#left_a2").toggleClass("left_aaa")
	$(".menu_ul4,.menu_ul6,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul2").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul2").css({"top":j1,"left":w})
		
	})
	$(".menu_ul2").slideToggle()
})
$(".menu_li2_a").click(function(){
	$(".menu_li2_a").toggleClass("hover_bg2")
	$(".menu_ul3").slideToggle()	
})

$('#left_a3').click(function(){
	$("#left_a1,#left_a2,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul6,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$('#left_a3').toggleClass("left_aaa")
	var t=$(this).offset().top-120
	var w=$(".left").width()
	$(".menu_ul4").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul4").css({"top":j1,"left":w})
		
	})
	$(".menu_ul4").slideToggle()
})


$('#left_a4').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$('#left_a4').toggleClass("left_aaa")
	var t=$(this).offset().top-220
	var w=$(".left").width()
	$(".menu_ul6").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul6").css({"top":j1,"left":w})
		
	})
	$(".menu_ul6").slideToggle()
})
$(".menu_li6_a").click(function(){
	$(".menu_li6_a1,.menu_li6_a2").removeClass("hover_bg6")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li6_a").toggleClass("hover_bg6")
	var t0=$(".menu_ul6").offset().top
	$(".menu_ul7").css('top',t0)
	$(".menu_ul7").slideToggle()	
})
$(".menu_li6_a1").click(function(){
	$(".menu_li6_a,.menu_li6_a2").removeClass("hover_bg6")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li6_a1").toggleClass("hover_bg6")
	var t0=$(".menu_ul6").offset().top
	$(".menu_ul7_0").css('top',t0)
	$(".menu_ul7_0").slideToggle()	
})
$(".menu_li6_a2").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li6_a,.menu_li6_a1").removeClass("hover_bg6")
	$()
	$(".menu_li6_a2").toggleClass("hover_bg6")
	var t0=$(".menu_ul6").offset().top
	$(".menu_ul7_3").css('top',t0)
	$(".menu_ul7_3").slideToggle()	
})

$(".menu_li7_a1").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li7_a2,.menu_li7_a3").removeClass("hover_bg7")
	$(".menu_li7_a1").toggleClass("hover_bg7")
	$(".menu_ul7_1").slideToggle()	
})
$(".menu_li7_a2").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_1,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li7_a1,.menu_li7_a3").removeClass("hover_bg7")
	$(".menu_li7_a2").toggleClass("hover_bg7")
	$(".menu_ul7_2").slideToggle()	
})

$('#left_a5').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$('#left_a5').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul6,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul8").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul8").css({"top":j1,"left":w})
		
	})
	$(".menu_ul8").slideToggle()
})

$('#left_a6').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a5,#left_a4,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$('#left_a6').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul6,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul10").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul10").css({"top":j1,"left":w})
		
	})
	$(".menu_ul10").slideToggle()
})

$('#left_a7').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a8,#left_a9").removeClass("left_aaa")
	$('#left_a7').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul6,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul12").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul12").css({"top":j1,"left":w})
		
	})
	$(".menu_ul12").slideToggle()
})

$('#left_a8').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a9").removeClass("left_aaa")
	$('#left_a8').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul15,.menu_ul6,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t1=$(".menu_ul14").height()
	var t=$(this).offset().top-t1+76
	var w=$(".left").width()
	$(".menu_ul14").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul14").css({"top":j1,"left":w})
		
	})
	$(".menu_ul14").slideToggle()
})

$(".menu_li14_a").click(function(){
	$(".menu_li14_a").toggleClass("hover_bg14")
	var t0=$(".menu_ul14").offset().top
	$(".menu_ul15").css('top',t0)
	$(".menu_ul15").slideToggle()	
})

$('#left_a9').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8").removeClass("left_aaa")
	$('#left_a9').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul6,.menu_ul17_0,.menu_ul17_1,.menu_ul17_2").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t1=$(".menu_ul16").height()
	var t=$(this).offset().top-t1+40
	var w=$(".left").width()
	$(".menu_ul16").css({"top":t,"left":w})
	$(window).scroll(function(){
		var j=$(window).scrollTop()
		var j1=t-j
		$(".menu_ul16").css({"top":j1,"left":w})
		
	})
	$(".menu_ul16").slideToggle()
})
$(".menu_li16_a").click(function(){
	$(".menu_li16_a1,.menu_li16_a2").removeClass("hover_bg16")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul17_1,.menu_ul17_2").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a").toggleClass("hover_bg16")
	$(".menu_ul17_0").slideToggle()	
})
$(".menu_li16_a1").click(function(){
	$(".menu_li16_a,.menu_li16_a2,.menu_li16_a3").removeClass("hover_bg16")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul17_0,.menu_ul17_2,.menu_ul17_3").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a1").toggleClass("hover_bg16")
	$(".menu_ul17_1").slideToggle()	
})
$(".menu_li16_a2").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul17_1,.menu_ul17_0,.menu_ul17_3").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a,.menu_li16_a1,.menu_li16_a3").removeClass("hover_bg16")
	$()
	$(".menu_li16_a2").toggleClass("hover_bg16")
	$(".menu_ul17_2").slideToggle()	
})
$(".menu_li16_a3").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul17_1,.menu_ul17_0,.menu_ul17_2").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a,.menu_li16_a1,.menu_li16_a2").removeClass("hover_bg16")
	$()
	$(".menu_li16_a3").toggleClass("hover_bg16")
	$(".menu_ul17_3").slideToggle()	
})

$('.right,.top,.main-content').click(function(){
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul6,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul15,.menu_ul16").hide()
	$(".left_a").removeClass("left_aaa")
});


//菜单栏下拉
var o=0;
var y=$('.left_a').length+1
var q=$(".left").height()/$('.left_a').height()
var k=Math.round(q)
var h=(y-k)*$('.left_a').height()
var a=$('.left_a').height()
	$('.left_ck_span2').click(function(){
		if(o<h){
			o=o+a
			$('.left_nav').animate({marginTop:-o})
		}
	})
	$('.left_ck_span1').click(function(){
		if(o>0){
			o=o-a
			$('.left_nav').animate({marginTop:-o})
		}
	})
	
	