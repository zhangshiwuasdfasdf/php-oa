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
}
function wli(){
	var wsp0=0;
	var wsp=$('.content_ul_w ul li').eq(0).children('span');
	for( var i=0; i<wsp.length;i++){
		wsp0+=wsp.eq(i).outerWidth();
	}
	$('.content_ul_w ul li').css('width',wsp0);	
}
wli();


$(".content_ajs").click(function(){
	$("#tc_js").show();
	$(".bottom_sp1").click(function(){
		$("#tc_js").hide();	
		location.reload();
	})
	$(".bottom_sp2").click(function(){
		$("#tc_js").hide();	
	})
})

$(".content_bj").click(function(){
	
	/*$('#bj1').text('编辑')*/
	$("#tc_qxbj").show();
	$(".bottom_sp3").click(function(){
		$("#tc_qxbj").hide();
		//location.reload();
	})
	$(".bottom_sp4").click(function(){
		$("#tc_qxbj").hide();
	})
})

$('.content_afz').click(function(){
	$("#tc_jsfz").show();
	$(".bottom_sp3,.bottom_sp4").click(function(){
		$("#tc_jsfz").hide();	
	})	
})

$("#a_qxtj").click(function(){
	/*$('#bj1').text('添加')*/
	$("#tc_qxtj").show();
	$(".bottom_sp3,.bottom_sp4").click(function(){
		$("#tc_qxtj").hide();	
	})
})

$(".tc_ul_s img").each(function(){
	$(this).toggle(function(){
		$(this).parent('li').children('ul').slideDown()
	},function(){
		$(this).parent('li').children('ul').slideUp()
	})
})


$("#js_sp4").click(function() {
  if(this.checked){                                     
    $("[name='role_id[]']:checkbox").prop("checked",true);  
  }else{
    $("[name='role_id[]']:checkbox").prop("checked",false);  
  }
});


$('#all').click(function(){
	if(this.checked){                                     
		$("[name='id[]']:checkbox").prop("checked",true);  
	}else{
		$("[name='id[]']:checkbox").prop("checked",false);  
	}	
});
