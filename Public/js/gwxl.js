// JavaScript Document

function wli(){
	var wsp0=0;
	var wsp=$('.content_ul_w ul li').eq(0).children('span');
	for( var i=0; i<wsp.length;i++){
		wsp0+=wsp.eq(i).outerWidth();
	}
	$('.content_ul_w ul li').css('width',wsp0);	
}
wli();


$(".content_a").click(function(){
	$("#tc_glbj").show();
	$(".bottom_sp3,.bottom_sp4").click(function(){
		$("#tc_glbj").hide();	
	})	
})
$("#a_gltj").click(function(){
	$("#tc_gltj").show();
	$(".bottom_sp3").click(function(){
		$("#tc_gltj").hide();	
	})
})

$('#all').click(function(){
	if(this.checked){                                     
		$("[name='id[]']:checkbox").prop("checked",true);  
	}else{
		$("[name='id[]']:checkbox").prop("checked",false);  
	}	
});