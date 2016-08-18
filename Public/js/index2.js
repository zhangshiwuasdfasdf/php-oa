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


function ww(){
	var w = $(".left").width()
	$(".menu_ul2").css('left',w)
	var	g = $(".menu_li1 a").width()-4
	$(".sanj").css('left',g+8)
}
ww();


//左边菜单栏
//个人中心
//$("#gr_ej,#gr_sj,#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej").hide()	


$("#gr_yj_a").click(function(){
	$("#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej,#rl_yj_a .sanj,#xz_yj_a .sanj,#zc_yj_a .sanj,#xx_yj_a .sanj,#da_yj_a .sanj,#zs_yj_a .sanj,#tj_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#gr_ej").slideToggle();
	$(".menu_li1_a").removeClass("bg_a")
	$("#gr_yj_a").toggleClass("bg_a")	
	$("#gr_yj_a .sanj").slideToggle();
})

$("#gr_ej_a").click(function(){
	$("#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej").hide()
	$("#gr_sj").slideToggle();
})

//人力资源管理
$("#rl_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej,#gr_yj_a .sanj,#xz_yj_a .sanj,#zc_yj_a .sanj,#xx_yj_a .sanj,#da_yj_a .sanj,#zs_yj_a .sanj,#tj_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#rl_ej").slideToggle();	
	$(".menu_li1_a").removeClass("bg_a")
	$("#rl_yj_a").toggleClass("bg_a")
	$("#rl_yj_a .sanj").slideToggle();
})

//行政管理
$("#xz_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_sj,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej,#rl_yj_a .sanj,#gr_yj_a .sanj,#zc_yj_a .sanj,#xx_yj_a .sanj,#da_yj_a .sanj,#zs_yj_a .sanj,#tj_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#xz_ej").slideToggle();	
	$(".menu_li1_a").removeClass("bg_a")
	$("#xz_yj_a").toggleClass("bg_a")
	$("#xz_yj_a .sanj").slideToggle();
})
$("#xz_ej_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej").hide()
	$("#xz_sj").slideToggle();	
})
$("#xz_sj_a1").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej").hide()
	$("#xz_sij1").slideToggle();	
})
$("#xz_sj_a2").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_sij1,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej").hide()
	$("#xz_sij2").slideToggle();	
})
$("#xz_sj_a3").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_sij1,#xz_sij2,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej").hide()
	$("#xz_sij3").slideToggle();	
})

//资产管理
$("#zc_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#xt_ej,#rl_yj_a .sanj,#xz_yj_a .sanj,#gr_yj_a .sanj,#xx_yj_a .sanj,#da_yj_a .sanj,#zs_yj_a .sanj,#tj_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#zc_ej").slideToggle();
	$(".menu_li1_a").removeClass("bg_a")
	$("#zc_yj_a").toggleClass("bg_a")
	$("#zc_yj_a .sanj").slideToggle();
})

//信息管理
$("#xx_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#da_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej,#rl_yj_a .sanj,#xz_yj_a .sanj,#zc_yj_a .sanj,#gr_yj_a .sanj,#da_yj_a .sanj,#zs_yj_a .sanj,#tj_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#xx_ej").slideToggle();
	$(".menu_li1_a").removeClass("bg_a")
	$("#xx_yj_a").toggleClass("bg_a")
	$("#xx_yj_a .sanj").slideToggle();	
})

//档案中心
$("#da_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#zs_ej,#tj_ej,#xt_ej,#zc_ej,#rl_yj_a .sanj,#xz_yj_a .sanj,#zc_yj_a .sanj,#xx_yj_a .sanj,#gr_yj_a .sanj,#zs_yj_a .sanj,#tj_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#da_ej").slideToggle();	
	$(".menu_li1_a").removeClass("bg_a")
	$("#da_yj_a").toggleClass("bg_a")
	$("#da_yj_a .sanj").slideToggle();
})

//知识中心
$("#zs_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#tj_ej,#xt_ej,#zc_ej,#rl_yj_a .sanj,#xz_yj_a .sanj,#zc_yj_a .sanj,#xx_yj_a .sanj,#da_yj_a .sanj,#gr_yj_a .sanj,#tj_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#zs_ej").slideToggle();	
	$(".menu_li1_a").removeClass("bg_a")
	$("#zs_yj_a").toggleClass("bg_a")
	$("#zs_yj_a .sanj").slideToggle();
})

//统计中心系统管理
$("#tj_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#xt_ej,#zc_ej,#rl_yj_a .sanj,#xz_yj_a .sanj,#zc_yj_a .sanj,#xx_yj_a .sanj,#da_yj_a .sanj,#zs_yj_a .sanj,#gr_yj_a .sanj,#xt_yj_a .sanj").hide()
	$("#tj_ej").slideToggle();
	$(".menu_li1_a").removeClass("bg_a")
	$("#tj_yj_a").toggleClass("bg_a")	
	$("#tj_yj_a .sanj").slideToggle();
})

//系统管理
$("#xt_yj_a").click(function(){
	$("#gr_ej,#gr_sj,#rl_ej,#xz_ej,#xz_sj,#xz_sij1,#xz_sij2,#xz_sij3,#xx_ej,#da_ej,#zs_ej,#tj_ej,#zc_ej,#rl_yj_a .sanj,#xz_yj_a .sanj,#zc_yj_a .sanj,#xx_yj_a .sanj,#da_yj_a .sanj,#zs_yj_a .sanj,#tj_yj_a .sanj,#gr_yj_a .sanj").hide()
	$("#xt_ej").slideToggle();
	$(".menu_li1_a").removeClass("bg_a")
	$("#xt_yj_a").toggleClass("bg_a")	
	$("#xt_yj_a .sanj").slideToggle();
})



//标签栏		
$(".img_a").click(function(){
	if($(".right_tb a").length <= 8){			
		$(".right_tb").append('<a class="right_zm2"><span></span><img class="tb_gb" src="img/tb_gb.png"/><img class="tb_sx" src="img/tb_sx_b.png"/><img class="tb_fh" src="img/tb_fh.png"/></a>')
	}else{
		alert("标签太多了，可以先删除一些！")
	}
	
		
	$(".tb_gb").click(function(){
	$(".tb_gb").index(this)
	$(this).parent(this).remove()
	})	
	
	$(".tb_gb").each(function(){
    $(this).mouseenter(function(){
		$(this).attr('src','img/tb_gb1_b1.png')
		$(this).css({"top":"6px","right":"4%"})
	})
		$(this).mouseleave(function(){
			$(this).attr('src','img/tb_gb.png')
			$(this).css({"top":"8px","right":"5%"})
		})
});
})
function sign(type){
	$.ajax({
		type:'get', 
		url: "./index.php?m=Common&a=sign",
		data:{type:type},
		dataType: "json",
		success: function(result){
			alert(result.msg);
			if(result.status==1){
				if(result.code=='in'){
					$(".top_menu_r #in").attr('style','display:none');
				}else{
					$(".top_menu_r #out").attr('style','display:none');
				}
			}
			
		},
		error:function(e){
			alert(result.msg);
		}
	});
}
