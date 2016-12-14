// JavaScript Document
function wli(){
	var win=($(window).width()-30)/3;
	$('.span2').css('width',win);
	
	var wsp0=0;
	var wsp=$('.content_ul_w ul li').eq(0).children('span');
	for( var i=0; i<wsp.length;i++){
		wsp0+=wsp.eq(i).outerWidth();
	}
	$('.content_ul_w ul li').css('width',wsp0);
}
wli();

$('#all').click(function(){
	if(this.checked){                                     
		$("[name=box]:checkbox").prop("checked",true);  
	}else{
		$("[name=box]:checkbox").prop("checked",false);  
	}	
});