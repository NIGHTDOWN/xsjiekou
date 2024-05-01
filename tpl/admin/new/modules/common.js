 ;layui.define(function(e){var i=(layui.$,layui.layer,layui.laytpl,layui.setter,layui.view,layui.admin);i.events.logout=function(){i.req({url:layui.setter.base+"json/user/logout.js",type:"get",data:{},done:function(e){i.exit(function(){location.href=""})}})},e("common",{})});
  $(function(){
					$obj2=$('.opbar a');
					
					$obj2.eq(0).parent('dd').trigger("click");
					$obj2.eq(0).trigger("click");;

				});
			