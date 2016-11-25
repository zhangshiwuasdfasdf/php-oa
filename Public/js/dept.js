
//以下为多选部门和岗位
	
$(function(){
    var M1 = $('#dept_name_multi').parent();
    M1.on('click',function(e){e.stopPropagation();})
    .find('div[class="ss"]').on('click',function(){
        M1.find('.content0').show();
    });
    $(document).on('click',function(){M1.find('.content0').hide()})
    
})


//checkbox
$("#qk").click(function(){
	$('div[class="content1"] input[type="checkbox"]').prop({
        checked: false
    })
})
$("#gb").click(function(){
	$('.content0').hide();
})
$("#qd").click(function(){
	
	var s = '';
	var data = '';
	$('div[class="content1"] input[type="checkbox"]:checked').each(function(){
		s += $(this).attr('name2')+';';
		data += $(this).val()+'|';
    });
	$("#dept_name_multi").val(s);
	$("#dept_name_multi_data").val(data);
    $('.content0').hide();
    
	$.ajax({
		type:'get', 
		url: "./index.php?m=common&a=get_depts_child",
		data:{dept_id_0:data},
		dataType: "json",
		success: function(result){
			$(".content2").html(result);
		},
		error:function(e){
		}
	});
})
$("#qx").click(function(){
	$('div[class="content1"] input[type="checkbox"]').prop({
        checked: false
    })
    $("#dept_name_multi").val('');
	$("#dept_name_multi_data").val('');
    $('.content0').hide();
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

$("div[class='content1'] li img").each(function(){
	$(this).toggle(function(){
		$(this).attr('src','./Public/img/hl.png')
		$(this).parent('li').children('ul').slideDown()
	},function(){
		$(this).attr('src','./Public/img/zk.png')	
		$(this).parent('li').children('ul').slideUp()
	})
})
//以上为多选部门和岗位