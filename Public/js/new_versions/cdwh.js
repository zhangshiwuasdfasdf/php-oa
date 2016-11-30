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
	var wsp=$('.content_div1').children('span');
	for( var i=0; i<wsp.length;i++){
		wsp0+=wsp.eq(i).outerWidth();
	}
	$('.content_div1,.content_div2').css('width',wsp0);
	//$(".isChild").next().hide();
}
wli();

$(".content_div2 img").each(function(){
	$(this).toggle(function(){
		$(this).parent().parent().children('ul').show()
		$(this).attr('src',"./Public/img/new_versions/ajj.png");
	},function(){
		$(this).parent().parent().children('ul').hide()
		$(this).attr('src','./Public/img/new_versions/add.png');
	})
})

$(".a1_1").click(function(){
	$("#bj1").text('修改')
	$("#tc_yjtj").show();
	$(".bottom_sp3").click(function(){
		$("#tc_yjtj").hide();
	})
})

$(".a1_3").click(function(){
	$("#tc_js").show();
	$(".bottom_sp1").click(function(){
		$("#tc_js").hide();
	})
})
$(".a1_4").click(function(){
	$("#tc_sj").show();
	$(".bottom_sp1").click(function(){
		$("#tc_sj").hide();	
	})
})
$(".a1_5").click(function(){
	$("#tc_jsfz").show();
	$(".bottom_sp3").click(function(){
		$("#tc_jsfz").hide();	
	})
})


$("#a_yjtj").click(function(){
	$("#bj1").text('添加')
	$("#tc_yjtj").show();
	$(".bottom_sp3").click(function(){
		$("#tc_yjtj").hide();	
	})	
})

$(".a1_0,.a1_1").click(function(){
	if($(this).text() == "添加"){
		$("#bottom_sp5").text("添加");
		$("#tc_ejtj input[name='menu_no']").val("")
		$("#tc_ejtj input[name='menu_name']").val("")
		$("#tc_ejtj input[name='menu_addr']").val("")
		var id = $(this).parent().parent().find(".li_sp3 input:eq(1)").val();
	}else{
		$("#bottom_sp5").text("修改");
		$("#menuId").val($(this).parent().parent().find(".li_sp3 input:eq(1)").val());
		var menu_no = $(this).parent().parent().find(".li_sp3 input:eq(3)").val();
		var menu_name = $(this).parent().parent().find(".li_sp0_2 span:eq(0)").text();
		var menu_addr = $(this).parent().parent().find("span:eq(3)").text();
		var id = $(this).parent().parent().find(".li_sp3 input:eq(2)").val();
		var sort = $(this).attr('msg').trim();
		if(id === "0"){$("select[name='pid']").prop("disabled",true);}
		$("#tc_ejtj input[name='menu_no']").val(menu_no)
		$("#tc_ejtj input[name='menu_name']").val(menu_name)
		$("#tc_ejtj input[name='menu_addr']").val(menu_addr)
		$("#tc_ejtj input[name='sort']").val(sort);
	}
	$("select[name='pid']").find("option[value="+id+"]").prop("selected","selected");
	$("#tc_ejtj").show();
	$(".bottom_sp3").click(function(){
		$("select[name='pid']").prop("disabled",false);
		$("#tc_ejtj").hide();
	})	
})


/*if($(".li_sp3").html()=="启用"){
	$("#a1_2").toggle(function(){	
		$(".li_sp3").text('禁用')
		$("#a1_2").text('启用')
	},function(){
		$(".li_sp3").text('启用')
		$("#a1_2").text('禁用')	
	})
}
else if($(".li_sp3").html()=="禁用"){
	$("#a1_2").toggle(function(){	
		$(".li_sp3").text('启用')
		$("#a1_2").text('禁用')
	},function(){
		$(".li_sp3").text('禁用')
		$("#a1_2").text('启用')	
	})		
}*/

$("#js_sp4,#js_sp5").click(function() {
  if(this.checked){                                     
    $("[name='role_id[]']:checkbox").prop("checked",true);  
  }else{
    $("[name='role_id[]']:checkbox").prop("checked",false);  
  }
});

$('#all').click(function(){
	if(this.checked){                                     
		$("[name=box]:checkbox").prop("checked",true);  
	}else{
		$("[name=box]:checkbox").prop("checked",false);  
	}	
});


