// JavaScript Document
function h0(){
	var h0=$(".qx_div").height()
	$(".qx_div_l").css('line-height',h0+'px')
	var h1=(h0-$(".qx_select").height())/2
	$(".qx_select").css('margin-top',h1+'px')
	var h2=(h0-$(".qx_inp").height())/2
	$(".qx_inp").css('margin-top',h2+'px')
}
h0();

$(".kq_inp").click(function() {
  if(this.checked){                                     
    $(this).parent().next().find("input[name=check_kq]:checkbox").prop("checked",true); 
  }else{
	  $(this).parent().next().find("input[name=check_kq]:checkbox").prop("checked",false); 
  }
});
/*$("[name=check_kq]:checkbox").click(function(){
	if(this.checked){
		$("#kq_inp").prop("checked",true);	
	}else{
		$("#kq_inp").prop("checked",false);  
	 }	
})

$("#qj_inp").click(function() {
  if(this.checked){                                     
    $("[name=check_qj]:checkbox").prop("checked",true);  
  }else{
    $("[name=check_qj]:checkbox").prop("checked",false);  
  }
});*/