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
//	var l1=$('#right_l1').height()+165+8;
//	var l2=$('#right_l2').height()+8;
//	var l3=$('#right_l3').height();
//	var z1=$(".right_z1_content1").height()+46+6+165+20;
//	var z2=$('#right_z2').height()+8;
//	var z3=$('#right_z3').height();
//	var r1=$('#right_r1').height()+165+30;
//	var r2=$('#right_r2_bg').height()+8;
//	var r3=$('#right_r3').height();
//	$('#right_l2').css('top',l1);
//	$('#right_l3').css('top',l1+l2);
////	$('#right_z2').css('top',z1);
//	$('#right_z3').css('top',z1+z2);
//	$('#right_r2_bg').css('top',r1);
//	$('#right_r3').css('top',r1+r2);
	
	var w=$(window).width()-125
	var q2=w*0.01
    var q4=w*0.004
    var q5=Math.round(q4)
    var q3=Math.round(q2)
	var s=w
	var x=s/3
    
    var st=Math.round(x)
    
	$('.right').css('width',w)
	$("#right_l1,#right_l2,#right_l3,#right_z2,#right_z3,#right_r1,#right_r2_bg,#right_r3,#right_z1").css({'width':st,'padding-left':q3,'padding-right':q3,'padding-top':q5,'padding-bottom':q5})
	
//	var stl=st+120+q3
//	$("#right_z1,#right_z2,#right_z3").css('left',stl)
//	
//	var stl2=st*2+q3*3+120
//	$("#right_r1,#right_r2_bg,#right_r3").css('left',stl2)

	
	/*$("#right_l1,#right_l2,#right_l3").css('padding',q2)
	
	$("#right_z1,#right_z2,#right_z3").css('padding',q2)
	$("#right_r1,#right_r2_bg,#right_r3").css('padding',q2)*/
	
	//个人信息距离左边值
	//var l=$('.sc').offset().left-120;
	//var q1=q2+120;
	//var l0=q1-120;	
	//var l1=l-q2
	//$('.sc1').css('marginLeft',l1)
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
	setInterval(up1,6000);
}

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

//添加模块
/*function checkbox()
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
	}else
	{
		
		for(var j=0; j<check_num; j++){
			var q=chestr.split(';')
			if(q[j]=='我的日报'){
                $(".bottom_ul").before('<div class="bottom_div1"><a class="bottom_a" id="bottom_a1"><img src="img/bottom_rb.png"/></a><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></div>')	
            }else if(q[j]=='我的周报'){
                $(".bottom_ul").before('<div class="bottom_div1"><a class="bottom_a" id="bottom_a1"><img src="img/bottom_zb.png"/></a><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></div>')
            }else if(q[j]=='我的月报'){
                $(".bottom_ul").before('<div class="bottom_div1"><a class="bottom_a" id="bottom_a1"><img src="img/bottom_yb.png"/></a><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></div>')
            }else if(q[j]=='组织架构'){
                $(".bottom_ul").before('<div class="bottom_div1"><a class="bottom_a" id="bottom_a1"><img src="img/bottom_jg.png"/></a><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></div>')
            }else if(q[j]=='今日头条'){
                $(".bottom_ul").before('<div class="bottom_div1"><a class="bottom_a" id="bottom_a1"><img src="img/bottom_tt.png"/></a><img class="tb_gb1_b1" src="img/tb_gb1_b1.png"/></div>')
            }else{
                $(".bottom_ul").append('<li><a class="bottom_a2">'+q[j]+'</a></li>')  
            }
			$(".tb_gb1_b1").click(function(){
				$(".tb_gb1_b1").index(this)
				$(this).parent(this).remove()
			})

		}
		
	}
}*/

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
	var url = $(this).children("#url").val();
	var user_id = $(this).children("#o_id").val();
	var position_id = $(this).children("#o_position_id").val();
	var dept_id = $(this).children("#o_dept_id").val();
	$.ajax({
		type : "POST",
		url : url,
		data : "user_id="+user_id+"&position_id="+position_id+"&dept_id="+dept_id+"&ajax=1",
		dataType : "json",
		beforeSend:function(){
			ui_info('loading...');
		},
		success : function(result){
			if(result.status=='1'){
				var m=$(this).children('span').eq(0).html();	
				var n=$(this).children('span').eq(2).html();
				var o=$(this).children('span').eq(4).html();
				$(".right_ge").children('span').eq(0).text(m);
				$(".right_ge").children('span').eq(2).text(n);
				$(".right_ge").children('a').text(o);
				$(".right_ge_x").slideUp();
				location.reload();
			}
			
		}
	});
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