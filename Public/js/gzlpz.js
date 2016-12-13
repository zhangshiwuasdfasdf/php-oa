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

$('#a_lbtj').click(function(){
	$('#tc_xzlc').show();
	$(".bottom_sp3,.bottom_sp4").click(function(){
		$("#tc_xzlc").hide();	
	});
});
$(".content_a").click(function(){
	$("#tc_bjlc").show();
	$(".bottom_sp3,.bottom_sp4").click(function(){
		$("#tc_bjlc").hide();	
	})	
})
$('#all').click(function(){
	if(this.checked){                                     
		$("[name='id[]']:checkbox").prop("checked",true);  
	}else{
		$("[name='id[]']:checkbox").prop("checked",false);  
	}	
});