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

$(".a_glbj,.a_copy").click(function(){
	if($(this).text() == "复制"){
		$("#tc_glbj select[name='company']").prop("disabled",true);
	}else{
		$("#tc_glbj .bottom_sp4").attr('msg',1);
		var sort = $(this).attr("msg").trim();
	}
	var obj = $(this).parent().parent();
	var id = obj.find("span input:eq(1)").val().trim();
	var company = obj.find("span input:eq(2)").val().trim();
	var name = obj.find("span:eq(2)").text().trim();
	var status = obj.find("span:eq(3)").attr("msg").trim();
	
	$("#tc_glbj input[name='id']").val(id);
	$("#tc_glbj select[name='company']").find("option[value='"+company+"']").prop("selected",true);
	$("#tc_glbj input[name='role_name']").val(name);
	$("#tc_glbj select[name='status']").find("option[value='"+status+"']").prop("selected",true);
	$("#tc_glbj input[name='sort']").val(sort);
	$("#tc_glbj").show();
	$(".bottom_sp3").click(function(){
		$("#tc_glbj select[name='company']").prop("disabled",false);
		$("#tc_glbj .bottom_sp4").attr('msg',"");
		$("#tc_glbj").hide();	
	})	
})
$("#a_gltj").click(function(){
	$("#tc_gltj input").val("");
	$("#tc_gltj").show();
	$(".bottom_sp3").click(function(){
		$("#tc_gltj").hide();	
	})
})


$('.a_cd').click(function(){
	var id = $(this).parent().parent().find("span.span2_0 input:eq(1)").val().trim();
	$("#tc_cd .bottom_qxql .bottom_sp2").attr('rid',id);
	$("#tc_cd").show();
	$(".bottom_sp1").click(function(){
		$("#tc_cd").hide();	
	})	
})

$('input[type="checkbox"]').change(function(e) {

  var checked = $(this).prop("checked"),
      container = $(this).parent(),
      siblings = container.siblings();

  container.find('input[type="checkbox"]').prop({
    indeterminate: false,
    checked: checked
  });

  function checkSiblings(el) {

    var parent = el.parent().parent(),
        all = true;

    el.siblings().each(function() {
      return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
    });

    if (all && checked) {

      parent.children('input[type="checkbox"]').prop({
        indeterminate: false,
        checked: checked
      });

      checkSiblings(parent);

    } else if (all && !checked) {

      parent.children('input[type="checkbox"]').prop("checked", checked);
      parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
      checkSiblings(parent);

    } else {

      el.parents("li").children('input[type="checkbox"]').prop({
        indeterminate: true,
        checked: false
      });

    }

  }

  checkSiblings(container);
});


$('#all').click(function(){
	if(this.checked){                                     
		$("[name=box]:checkbox").prop("checked",true);  
	}else{
		$("[name=box]:checkbox").prop("checked",false);  
	}	
})