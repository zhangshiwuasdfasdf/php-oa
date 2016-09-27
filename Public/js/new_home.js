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
  
//日期
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
	var time=year+"年"+month+"月"+date+"日"+" "+week;
	$("#left_span").html(time)
}
getNow();


function he(){
	//left的高度
	var h0=$(window).height()
	var h=h0-200
	$(".left").css('height',h)
	var hc=h+160	
	$(".left_ck").css('top',hc)
	
	var w=$(window).width()-$(".left").width()
	var q2=$(window).width()*0.01
	var q1=$(window).width()*0.01+$(".left").width()
	var s=w-$(window).width()*0.04
	var x=s/3
	$('.right').css('width',w)
	$(".right_l1,.right_l2,.right_l3,.right_z1,.right_z2,.right_z3,.right_r1,.right_r2_bg,.right_r3").css('width',x)
	$(".right_l1,.right_l2,.right_l3").css('marginLeft',q1)
	
	$(".right_z1,.right_z2,.right_z3").css('marginLeft',q2)
	$(".right_r1,.right_r2_bg,.right_r3").css('marginLeft',q2)
	
	//个人信息距离左边值
	var l=$('.sc').offset().left-120;
	var l0=q1-120;	
	var l1=l-l0
	$('.sc1').css('marginLeft',l1)
	
	var a0=$(".right_z1").height()
	$(".right_l1,.right_l2,.right_l3,.right_z2,.right_z3,.right_r1,.right_r2_bg,.right_r3").css('height',a0) 
	
}
he();

/*$(window).scroll(function(){
	var a0=$(window).height()-$(".left").offset().top-40
	$(".left").css('height',a0)	
})*/

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

	

//滚动
if($(".right_l2_ul li").length>5){
function up1(){
	$(".right_l2_ul").animate({marginTop:'-25px'},2000,
	function(){
		$(".right_l2_ul").css({marginTop:0})
		$(".right_l2_ul li:first").insertAfter($(".right_l2_ul li:last"))	
	})	}
setInterval(up1,6000);}

if($("#r2_ul_1 li").length>2){
function up2(){
	$("#r2_ul_1").animate({marginTop:-24},4000,
	function(){
		$("#r2_ul_1").css({marginTop:0})
		$("#r2_ul_1 li:first").insertAfter($("#r2_ul_1 li:last"))	
	})	}
setInterval(up2,6000);}

if($("#r2_ul_2 li").length>2){
function up3(){
	$("#r2_ul_2").animate({marginTop:-24},4000,
	function(){
		$("#r2_ul_2").css({marginTop:0})
		$("#r2_ul_2 li:first").insertAfter($("#r2_ul_2 li:last"))	
	})	}
setInterval(up3,6000);}


//菜单栏
$("#left_a1").toggle(function(){
	$("#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$("#left_a1").toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul6,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	var t=$(this).offset().top+12
	var w=$(".left").width()
	$(".ani_ss").css({"top":t,"left":w})
	$(".ani_ss").animate({opacity:1,width:'210px'})
},function(){
	$(".ani_ss").animate({opacity:0,width:'0px'})
	}
)

$("#left_a2").click(function(){
	$("#left_a1,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$("#left_a2").toggleClass("left_aaa")
	$(".menu_ul4,.menu_ul6,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul2").css({"top":t,"left":w})
	$(".menu_ul2").slideToggle()
})
$(".menu_li2_a").click(function(){
	$(".menu_li2_a").toggleClass("hover_bg2")
	$(".menu_ul3").slideToggle()	
})

$('#left_a3').click(function(){
	$("#left_a1,#left_a2,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul6,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$('#left_a3').toggleClass("left_aaa")
	var t=$(this).offset().top-120
	var w=$(".left").width()
	$(".menu_ul4").css({"top":t,"left":w})
	$(".menu_ul4").slideToggle()
})


$('#left_a4').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a5,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$('#left_a4').toggleClass("left_aaa")
	var t=$(this).offset().top-220
	var w=$(".left").width()
	$(".menu_ul6").css({"top":t,"left":w})
	$(".menu_ul6").slideToggle()
})
$(".menu_li6_a").click(function(){
	$(".menu_li6_a1,.menu_li6_a2").removeClass("hover_bg6")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li6_a").toggleClass("hover_bg6")
	$(".menu_ul7").slideToggle()	
})
$(".menu_li6_a1").click(function(){
	$(".menu_li6_a,.menu_li6_a2").removeClass("hover_bg6")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li6_a1").toggleClass("hover_bg6")
	$(".menu_ul7_0").slideToggle()	
})
$(".menu_li6_a2").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li6_a,.menu_li6_a1").removeClass("hover_bg6")
	$()
	$(".menu_li6_a2").toggleClass("hover_bg6")
	$(".menu_ul7_3").slideToggle()	
})

$(".menu_li7_a1").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li7_a2,.menu_li7_a3").removeClass("hover_bg7")
	$(".menu_li7_a1").toggleClass("hover_bg7")
	$(".menu_ul7_1").slideToggle()	
})
$(".menu_li7_a2").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_1,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li7_a1,.menu_li7_a3").removeClass("hover_bg7")
	$(".menu_li7_a2").toggleClass("hover_bg7")
	$(".menu_ul7_2").slideToggle()	
})

$('#left_a5').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a6,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$('#left_a5').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul6,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul8").css({"top":t,"left":w})
	$(".menu_ul8").slideToggle()
})

$('#left_a6').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a5,#left_a4,#left_a7,#left_a8,#left_a9").removeClass("left_aaa")
	$('#left_a6').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul6,.menu_ul12,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul10").css({"top":t,"left":w})
	$(".menu_ul10").slideToggle()
})

$('#left_a7').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a8,#left_a9").removeClass("left_aaa")
	$('#left_a7').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul6,.menu_ul14,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t=$(this).offset().top
	var w=$(".left").width()
	$(".menu_ul12").css({"top":t,"left":w})
	$(".menu_ul12").slideToggle()
})

$('#left_a8').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a9").removeClass("left_aaa")
	$('#left_a8').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul6,.menu_ul16").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t1=$(".menu_ul14").height()
	var t=$(this).offset().top-t1+76
	var w=$(".left").width()
	$(".menu_ul14").css({"top":t,"left":w})
	$(".menu_ul14").slideToggle()
})

$('#left_a9').click(function(){
	$("#left_a1,#left_a2,#left_a3,#left_a4,#left_a5,#left_a6,#left_a7,#left_a8").removeClass("left_aaa")
	$('#left_a9').toggleClass("left_aaa")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul6,.menu_ul17_0,.menu_ul17_1,.menu_ul17_2").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	var t1=$(".menu_ul16").height()
	var t=$(this).offset().top-t1+60
	var w=$(".left").width()
	$(".menu_ul16").css({"top":t,"left":w})
	$(".menu_ul16").slideToggle()
})
$(".menu_li16_a").click(function(){
	$(".menu_li16_a1,.menu_li16_a2").removeClass("hover_bg16")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul17_1,.menu_ul17_2,.menu_ul17_3").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a").toggleClass("hover_bg16")
	$(".menu_ul17_0").slideToggle()	
})
$(".menu_li16_a1").click(function(){
	$(".menu_li16_a,.menu_li16_a2,.menu_li16_a3").removeClass("hover_bg16")
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_2,.menu_ul7_3,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul17_0,.menu_ul17_2,.menu_ul17_3").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a1").toggleClass("hover_bg16")
	$(".menu_ul17_1").slideToggle()	
})
$(".menu_li16_a2").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul17_1,.menu_ul17_0,.menu_ul17_3").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a,.menu_li16_a1,.menu_li16_a3").removeClass("hover_bg16")
	$()
	$(".menu_li16_a2").toggleClass("hover_bg16")
	$(".menu_ul17_2").slideToggle()	
})
$(".menu_li16_a3").click(function(){
	$(".menu_ul2,.menu_ul3,.menu_ul4,.menu_ul7,.menu_ul7_0,.menu_ul7_1,.menu_ul7_2,.menu_ul8,.menu_ul10,.menu_ul12,.menu_ul14,.menu_ul17_1,.menu_ul17_0,.menu_ul17_2").hide()
	$(".ani_ss").animate({opacity:0,width:'0px'})
	$(".menu_li16_a,.menu_li16_a1,.menu_li16_a2").removeClass("hover_bg16")
	$()
	$(".menu_li16_a3").toggleClass("hover_bg16")
	$(".menu_ul17_3").slideToggle()	
})




//菜单栏下拉
var o=0;
var y=$('.left_a').length
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

//添加模块
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
            if(q[j]=='我的日报'){
                $(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_rb.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')	
            }else if(q[j]=='我的周报'){
                $(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_zb.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')
            }else if(q[j]=='我的月报'){
                $(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_yb.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')
            }else if(q[j]=='组织架构'){
                $(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_jg.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')
            }else if(q[j]=='今日头条'){
                $(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_tt.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')
            }else{
                $(".bottom_ul").append('<li><a class="bottom_a2">'+q[j]+'</a></li>')  
            }
			$(".tb_gb1_b1").click(function(){
				$(".tb_gb1_b1").index(this)
				$(this).parent(this).remove()
			})
			
			/*if($("#bottom_a1").is(':hidden')){
				$(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_rb.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')	
			}
			if($("#bottom_a2").is(':hidden')){
				$(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_zb.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')	
			}
			if($("#bottom_a3").is(':hidden')){
				$(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_yb.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')	
			}
			if($("#bottom_a4").is(':hidden')){
				$(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_jg.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')	
			}
			if($("#bottom_a5").is(':hidden')){
				$(".bottom_ul").before('<a class="bottom_a"><img src="img/bottom_tt.png"/><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></a>')	
			}*/
		}
		
	}
}

var check_num = 0;
var check_li = $(".bottom_ul li").length
function check(){ 
	if(event.srcElement.checked==true)
	check_num++;
	else
	check_num--;   
	if(check_num>4)
	{
		alert("最多只能选4个！");
		event.srcElement.checked=false;
		check_num--;
	} 
}

$(".tb_gb1_b1").click(function(){
	$(".tb_gb1_b1").index(this)
	$(this).parent(this).remove()
})


$(".bottom_tj_span").click(function(){
	$(".bottom_ul").children("li").remove()
	
	$(".bottom_tc_bg").show();
	
	$(".bottom_span1,.bottom_span2").click(function(){
		$(".bottom_tc_bg").hide();
	})
})

//企业公告切换
$(".qb").addClass("qb_bg")
$(".qb").click(function(){
	$("#z2_ul2,#z2_ul3,#z2_ul4").hide();
	$(".wd,.zd,.tz").removeClass("qb_bg")
	$(".qb").addClass("qb_bg")
	$("#z2_ul1").show()	
})
$(".wd").click(function(){
	$("#z2_ul1,#z2_ul3,#z2_ul4").hide();
	$(".qb,.zd,.tz").removeClass("qb_bg")
	$(".wd").addClass("qb_bg")
	$("#z2_ul2").show()	
})
$(".zd").click(function(){
	$("#z2_ul2,#z2_ul1,#z2_ul4").hide();
	$(".wd,.qb,.tz").removeClass("qb_bg")
	$(".zd").addClass("qb_bg")
	$("#z2_ul3").show()	
})
$(".tz").click(function(){
	$("#z2_ul2,#z2_ul3,#z2_ul1").hide();
	$(".wd,.zd,.qb").removeClass("qb_bg")
	$(".tz").addClass("qb_bg")
	$("#z2_ul4").show()	
})

//个人信息选择
$(".grxx_xial").click(function(){
	$(".right_ge_x").slideToggle();			
})

$('.right_ge1').click(function(){
	var m=$(this).children('span').eq(0).html();	
	var n=$(this).children('span').eq(2).html();
	var o=$(this).children('span').eq(4).html();
	$(".right_ge").children('span').eq(0).text(m);
	$(".right_ge").children('span').eq(2).text(n);
	$(".right_ge").children('a').text(o);
	$(".right_ge_x").slideUp();
})

//便签
$("#i2_1").click(function(){
	$("#i2_bg1").show();
	$("#i2_span1").click(function(){
		$("#i2_bg1").hide();
	})
	$("#i2_span2").click(function(){
		$("#i2_bg1").hide();
	})
})
$("#i2_2").click(function(){
	$("#i2_bg2").show();
	$("#i2_span3").click(function(){
		$("#i2_bg2").hide();
	})
	$("#i2_span4").click(function(){
		$("#i2_bg2").hide();
	})	
})