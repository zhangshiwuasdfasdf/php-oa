// JavaScript Document

function ww(){
	var w = $(".left").width()+162
	$(".menu_li_ws").css('left',w)
	var	g = $(".left a").width()-4
	$(".sanj").css('left',g)
	var x = $(".left").width()+323
	$(".menu_li_wi").css('left',x)
}
ww();


//左边菜单栏	
$(".left a").toggle(function(){
	$(".left a").removeClass("click_ys")
	$(".left_menu_ul_ej li,.left_menu_ul_sj li,.menu_li_we span,.sanj").hide()
	
	var h=$(this).offset().top
    $(".menu_li_we,.menu_li_ws,.menu_li_wi").css('top',h)
	
	var n=$(".left a").index(this)
	$(this).addClass("click_ys")
	$(".left_menu_ul_ej li").eq(n).slideDown()
	$(".sanj").eq(n).show()

},function(){
	$(this).removeClass("click_ys")
	$(".left_menu_ul_ej li,.left_menu_ul_sj li,.menu_li_we span,.sanj,.left_menu_ul_ij li").slideUp()
	
})



//一级菜单
$(".menu_li_we a").click(function(){

	$(".left_menu_ul_sj li,.menu_li_we span").hide()
	
	var n2=$(".menu_li_we a").index(this)
	$(".left_menu_ul_sj li").eq(n2).show()
	$(".menu_li_we span").eq(n2).show()
	
	/*$(".left_menu_ul_sj li").click(function(){
		$(".left_menu_ul_ej li,.left_menu_ul_sj li,.sanj").hide()
		$(".left a").removeClass("click_ys")
	})*/
	
})

//二级菜单
$(".menu_li_ws a").hover(function(){
	$(".menu_li_ws span").hide();
	
	var n3=$(".menu_li_ws a").index(this)
	$(".menu_li_ws span").eq(n3).show();
},function(){
	$(".menu_li_ws span").hide();
	}
)
		
//三级菜单
$("#gg_a").click(function(){
	$("#gk,#hys").hide()
	$("#gg").slideDown()
})
$("#gk_a").click(function(){
	$("#gg,#hys").hide()
	$("#gk").slideDown()	
})
$("#hys_a").click(function(){
	$("#gk,#gg").hide()
	$("#hys").slideDown()	
})
$(".menu_li_wi a").hover(function(){
	var n4=$(".menu_li_wi a").index(this)
	$(".menu_li_wi span").eq(n4).show()	
},function(){
		$(".menu_li_wi span").hide()
	}
)

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

