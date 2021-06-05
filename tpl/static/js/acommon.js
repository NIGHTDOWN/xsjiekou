/*
*author by : oe 夜
*/
//标签设置 clas属性值可以为notnull 、onlynum、onlyen、onlycn、jqform、mail\
 var prevobjval;
//试dom对象触发事件
var prevobj;
var boxobj=null;
var loadobj=null;
var loadobjid='loadid';
function showload(){
  loadobj=$("#" +loadobjid);
  if(loadobj.html()==undefined){
    $e=$('<div id="'+loadobjid+'" style="position:fixed;width:100%;height:100%;top:0px;z-index:9999;cursor:move;"><img src="/tpl/static/images/load.gif"  style="left:calc(50%);position:relative;top:calc(50%);"/></div>');
    $('body').append($e);
    loadobj=$("#" +loadobjid);
  }else{
    loadobj.remove();
    showload();
  }
}
function hideload(){
    loadobj.remove();
    
}
//获取提示框html
function getaddelemnet(text){
    $element='<div class="sysinfoelement" style="color:red;position: absolute;margin-top:-5px;z-index:999">'+text+'</div>';
    return $element;
}
function addchild(val,text,$id){
  if($id){
    $obj=$id;
  }else{
    $obj=$("#box");
  }
	
	$a=$obj.find('[name=parentid]');
	//$a.prepend('<option vlaue="'+val+'" >'+text+'</option>');
	$a.find("option:selected").attr('selected','');
	$a.find("[value="+val+"]").attr('selected','selected');
	msgbox('添加['+text+']子菜单',$obj);
	}
//ajax文件上传
function fileajax(ur,obj,fun){
  
      var data = new FormData();
      
         showload();
        $.each(obj[0].files, function(i, file) {
             data.append('upload_file'+i, file);
         });
         //data.append('name',whichichoose.val());

        $.ajax({
            url:ur,
            type:'POST',
            data:data,
            cache: false,
            contentType: false,        //不可缺参数
            processData: false,        //不可缺参数
            success:function(data){
                hideload();
                fun(data,status);},
            error:function(){}
            });
}
function jta(json){
     
   try{$a=json_to_array(json);
   return $a;
   }catch(e){
    return json;
   }
   
   
   
   
   
   
}
function json_to_array(json){
    var myObject = eval('(' + json + ')'); 
    return myObject;
}

function atj(arr){
  return array_to_json(arr); 
}
function array_to_json(arr){
    
}
//构建要上传的资源
function makePost($ar){
    $val='';
  $.each($ar,function(name,value){
                $val+="'"+name+"':'"+value+"',";
              
            });   
    $val="({"+$val+"})";
    $val=eval($val);
   return $val;
    
}
//alert调试
function adebug(msg){
   
  alert(msg);  
}
//调试
function cdebug(msg){
    console.log('****************'); 
 //    var object=window.event;  
//     console.log(object);  
  console.log(msg);  
  console.log('****************');  
}
//循环操作
function yLoop($arr){
     $.each($arr,function(name,value){
          
              
            }); 
}


 //普通ajax请求
 function yAjax(ur,ar,fun){
            //$.get(ur);
            showload();
          ar=makePost(ar);
          $.post(ur,
            ar,
            function(data,status){
             //   alert(fun);
             hideload();
              if(fun==null)
              {
                
                
                jqformdeal(data);}  
              else
              {fun(data,status);}  
              
            });
          
        }

//各种输入框检查事件
$(function(){
    
    $(document).on('keyup','input',function(){
    $classname=$(this).attr('clas');
    $out=null;
    str=$(this).val();
    switch ($classname) {
      case 'onlynum' :
       	$(this).val(str.replace(/\D/gi, ""));
       
        ;break;
        //只能输入英文
         case 'onlyen' :
       		$(this).val(str.replace(/\W/gi, ""));
        ;break;
        //只能输入中文
           case 'onlycn' :
      	$(this).val(str.replace(/[^\u4E00-\u9FA5]/gi, ""));
        ;break;
        }});
    
    
    
    
    
    
$(document).on('blur','input',function(){
    $classname=$(this).attr('clas');
    $out=null;
    switch ($classname) {
       case 'notnull' :
       if($(this).val()==""){$out=getaddelemnet('不能为空');};
       break;
       case 'mail' :
       var bo=/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/.test($(this).val());
		if (bo == false) {$out=getaddelemnet('邮箱格式不正确');};
		
       break;
       case 'phone' :
       	var bo = /^0{0,1}1[3|4|5|6|7|8|9][0-9]{9}$/.test($(this).val());
        	if (bo == false) {$out=getaddelemnet('手机格式不正确');}
        ;break;
        //只能输入数字
       
       case 'password' :
       if($(this).val()==""){};
       break;
    }
    $(this).after($out);
    
    
});
$(document).on('focus','input',function(){
    $element=$(this);
    $(this).next('.sysinfoelement').remove();
    //$element.remove();
});  
//系统添加的元素相应，回到聚焦当前的输入框
$(document).on('blur','.sysinfoelement',function(){
    $element=$(this);
    $(this).prve('input').focus();
    $element.remove();
});        
});        
 //菜单事件
$(function () {
    var interval;
    $('.navLists li').live('mouseover',function(){
        $(this).prevAll().children('ul').hide();
        $(this).nextAll().children('ul').hide();
        $(this).children('ul').show(); 
    });
    $('.navLists li').live('mouseout',function(){$(this).children('ul').hide();});
    $('.navLists li ul').live('mouseout',function(){$(this).hide();});
    ////////////////////
    
    $(".nav_stand2 .nav_tit").mouseover(function () {
        if ($(this).attr("id") != "home_nav_tit") {
            clearTimeout(interval);
            $(".nav_tit").addClass('navTitOn');
            $(".dlList").removeClass('no').addClass('on');
        }
    })
    $(".nav_stand2").mouseleave(function () {
        if ($(this).attr("id") != "home_nav_stand") {
            interval = setTimeout(function () {
                $(".dlList").addClass('no').removeClass('on');
                $(".nav_tit").removeClass('navTitOn');
            }, 10);
        }
    });
});
//添加收藏夹
function addfavorite(){
    	var link = window.location.href;
		var title = window.document.title;
		if (document.all) {
			window.external.addFavorite(link, title);
		}
		else if (window.sidebar) {
			window.sidebar.addPanel(title, link, "");
		}
    
    
}
//返回顶部
function backtop(){
    $('body,html').animate({scrollTop:0},1000);
    return false;
}
//浏览器类JS
//获取浏览器的宽度跟高度
function getbrowerinfo(){
    window.screen.width+"x"+window.screen.height
    }
//添加新的  删除   全选function checkAll(e, itemName){
  

$(document).on('click','button',function(){
    $classname=$(this).attr('clas');
    //$out;
    $obj=$(this).parent();
    switch ($classname) {
        
       case 'add' :$(this).after($obj.html());break;
       case 'del' :$obj.remove();break;
       case 'upall' :
       //$obj.remove();break;
     
       $name=$(this).attr('class');
       $url="'"+'index.php?m=admin&c=upimg'+"'";
       $obj="$('#"+$name+"')";
      
       $ob=eval($obj);
      
        if($ob.html()==undefined){
       $html='<input id="'+$name+'" type="file" accept="image/jpeg" value="" pattern="required" style="display:none" onchange="fileajax('+$url+','+$obj+',viewimg_all)">';
      $(this).before($html);
      }
       //$(this).on('click',add($('#'+$name)));
       add($('#'+$name),this);
       
       
       
       
       ;break;
        case 'upone' :
       //$obj.remove();break;
     
       $name=$(this).attr('class');
       $url="'"+'index.php?m=admin&c=upimg'+"'";
       $obj="$('#"+$name+"')";
      
       $ob=eval($obj);
      
        if($ob.html()==undefined){
       $html='<input id="'+$name+'" type="file" accept="image/jpeg" value="" pattern="required" style="display:none" onchange="fileajax('+$url+','+$obj+',viewimg_one)">';
      $(this).before($html);
      }
       //$(this).on('click',add($('#'+$name)));
       add($('#'+$name),this);
       
       
       
       
       ;break;
       
       
       
       case 'selectall' :
       var aa = document.getElementsByName($(this).attr('value'));
       for (var i=0; i<aa.length; i++){
        aa[i].checked = $(this).attr('checked');}
        break;
      
    }
    
    
    
});
//标题跑马灯

var gtitle=null;
var stitle=null;
function title(title)
{   
    if(title!=null){
        stitle=title;
        title = title.substring(1, title.length);
        document.title = title;  
    }
else{
    if(gtitle.length>1){setTimeout("title()",1000);}else{title(stitle);}
    
}

}
//js拖动
$(document).on('mousedown','.move',function(event){

  
            var obj=$(this);
         
           $top=obj.css('top').replace('px','');
           
            $offtop=(event.clientY-$top);
            
            if($offtop>35){}else{
          // if(0){}else{
               ofy=(event.clientY-obj[0].offsetTop);
               ofx=(event.clientX-obj[0].offsetLeft);
               
           
           var patch=parseInt($(this).css("height"))/2; /* 也可以写成var patch=parseInt($(this).css("width"))/2*/
            $(document).mousemove(function (event){
            
            var ox=event.clientX;
            var oy=event.clientY;
            //获取点到弹出框本身的位置
               
        
             // $("p:last").offset({ top: ofy, left: ofx });
              obj.css('top',oy-ofy);
            obj.css('left',ox-ofx);
            });
            
            $(document).mouseup(function (){
            $(this).unbind("mousemove");
            });  
    }
    
    
});

/**
 * 设置主页
 * @param:: string obj
 * @param:: string vrl
*/
function sethomepage(obj, vrl){
	try{
		obj.style.behavior='url(#default#homepage)';obj.setHomePage(vrl);
	}
	catch(e){
		if(window.netscape) {
			try { 
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");  
			}  
			catch (e){ 
				alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。");
			}
			var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
			prefs.setCharPref('browser.startup.homepage',vrl);
		}
	}
}

/* 随机数 */
function get_rndnum(n) {
	var chars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	var res = "";
	for(var i = 0; i < n ; i ++) {
		var id = Math.ceil(Math.random()*35);
		res += chars[id];
	}
	return res;
}


/* 只能由汉字，字母，数字和下横线组合 */
function check_userstring(str){  
    var re1 = new RegExp("^([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|_|[a-zA-Z0-9])*$");
	if(!re1.test(str)){
		return false;
	}else{
		return true;
	}
}

/* 判断字符长度，一个汉字为2个字符 */
function strlen(s){
	var l = 0;
	var a = s.split("");
	for (var i=0;i<a.length;i++){
		if (a[i].charCodeAt(0)<299){
			l++;
		}else{
			l+=2;
		}
	}
	return l;
}

/* 判断所选择数量 */
function check_count(id, my , num){
	var oEvent = document.getElementById('em_' + id + '_edit');
	var chks = oEvent.getElementsByTagName("INPUT");
	var count = 0;
	for(var i=0; i<chks.length; i++){
		if(chks[i].type=="checkbox"){
			if(chks[i].checked == true){
				count ++;
			}
			if(count > num){
				my.checked = false;
				alert('最多只能选择' + num + '项');
				return false;
			}
		}
	}
}

//传入要点击的元素，跟本元素
function add(obj,pobj){

    prevobj=pobj;
    
  obj.click();  
}
//格式化输入
//弹出框


function msgbox(title,obj,width,height,left,top,index){
    
   
   if(width==undefined ||width==''){width='';}
    if(height==undefined ||height==''){height='';}
     if(left==undefined ||left==''){left='22%';}else{left+='px';}
      if(top==undefined ||top==''){top='10%';}else{top+='px';}
      if(index==undefined ||index==''){index='9';}else{}
   
    
      if(boxobj==null){
        $boxs='<div id="msgbox" class="move"  style="width:'+width+'px;z-index:'+index+';height:'+height+'px;background-color: rgba(0, 0, 0, 0.05);position:fixed;left:'+left+';top:'+top+';padding:8px;overflow: hidden;"><div class="mggbox_1">';
        $boxtitle='<div class="msgtitle"><div  id="boxtitle"  >'+title+'</div><div  id="boxclose" onclick="closebox()">×</div></div><div style="clear:both" class="msgclear"></div>';
       
        $boxe='</div></div>';
      if(typeof(obj)=='object'){
        $boxbody='<div style="overflow: hidden;" id="boxbody">'+obj.html()+'</div>';
   
    }else{
       $boxbody='<div style="overflow: hidden;" id="boxbody">'+obj+'</div>';
       
    }
 if(title!=''){
    $box=$boxs+$boxtitle+$boxbody+$boxe;
 }else{
    $box=$boxs+$boxbody+$boxe; 
 }
    $('body').prepend($box);
  
    boxobj=$('#msgbox');

    }
    boxobj.find('input:first').focus();
    
    
    
}


//tools
function tools_select(name,obj){
	//var obj=document.elementFromPoint(event.clientX,event.clientY);
	$('input[name=\''+name+'\']').attr('checked',$(obj).hasClass('icon-checkbox-unchecked'));
	$(obj).toggleClass('icon-checkbox-unchecked');
	return false;
}
//工具栏提交
//表单无刷新核心
//将form转为AJAX提交
function ajaxSubmit(frm, fun) {
    
    var dataPara = getFormJson(frm);
    showload();
//
//fun=fun+"()";
  //adebug('dd');
    $.ajax({
        url: frm.attr('action'),
        type: frm.attr('method'),
        data: dataPara,
        success:function(dataPara){
            hideload();
     
            dataPara=jta(dataPara);
            //cdebug(dataPara);
            if(typeof(dataPara)=='object'){                                    
            fun=fun+'(dataPara);';
                         
            eval(fun);}else{
              fun=fun+'(dataPara);';
                           
            eval(fun);  
            }            
        }
                        
    });
}

//工具栏提交
function tools_submit(obj)
{

    var form = $('form[action=""]');
    //$('<form></form>');
    //$(this).parentsUntil('form');
    //cdebug(this);
    //$('form:first');
    var confirm_flag = true;
    //cdebug(form);
    if (obj != undefined)
    {
        if (obj['form'] != undefined) form = $(obj['form']);
        if (obj['action'] != undefined) form.attr('action', obj['action']);
        if (obj['method'] != undefined)
        {
            if (obj['method'] == 'get' || obj['method'] == 'post')
            {
                form.attr('method', obj['method']);
            }
            else form.attr('method', 'post');
        }
        else form.attr('method', 'post');
        if (form.attr('method') == 'get')
        {
            var pattern = /(\w+)=(\w+)/ig;
            var parames = {};
            obj['action'].replace(pattern, function (a, b, c) { parames[b] = c; });
            for (par in parames) form.append("<input type='hidden' name='" + par + "' value='" + parames[par] + "'>");
        }
        if (obj['msg'] != undefined) confirm_flag = true;
    }

    if (confirm_flag)
    {
        var select_id = obj['id'];

        if (obj['select_id'] != undefined) select_id = obj['select_id'];
        //alert('你确认删除操作？', function(){
        //  cdebug(select_id);
        //cdebug('ddd');
        if ($("input[name='" + select_id + "[]']:checked").size() > 0) { form.submit(); }
        else { msgbox('消息提示', "<p class='warning'>没有选择任何项目，无法删除</p>"); }

    } else
    {
        //cdebug('form'); 
        form.submit();
    }
    return false;
}
//将form中的值转换为键值对。
function getFormJson(frm) {
    var o = {};
  // cdebug(frm);
    var a = frm.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });

    return o;
}

//无刷新表单提交



//传入表单jq对象
//function jqsubmit(obj,fun) {
////ajaxSubmit(obj,fun);
//
//  obj.bind('submit', function(){
//        ajaxSubmit(this,fun);
//        return false;
//    });
//
//
//}
$(function(){
   fun='jqformdeal'; 
   
  
     //这里设置回调函数
    $(document).on('submit','[clas=jqform]',function(){
   //$('[clas=jqform]').bind('submit', function(){
    //cdebug($(this).attr('clas'));
 
       if($(this).attr('clas')=='jqform'){
          if($(this).attr('fun')!=undefined){
          fun =$(this).attr('fun'); 
        }
        
         ajaxSubmit($(this),fun);
        return false;
        
       }
       else{
        $(this).submit();
       }
           
       
  });  
})
//处理jq提交的form的数据
function jqformdeal($data){
  if(typeof($data)=='string'){
    $data=jta($data);
    if(typeof($data)=='string'){
        
    }
   };
   //$r=jta($data);
   //把产品id添加到当前的form
   //if($r.productid)
   $('[name=actid]').val($data.actid);
   $('[name=productid]').val($data.productid);
  // adebug($data.productid);
 
   if($data.errorid!=null){$msg=errcode[$data.errorid];
  
   adebug($msg); }
   if($data.msg!=null){
  
   adebug($data.msg); }
   if($data.url){
  
  window.location.href= $data.url;}
}
//刷新
function tools_reload(){
	location.reload();
}
//提交前咨询
function confirm_action(url,msg){
	if(msg==undefined) msg = '你确认删除操作吗？删除后无法恢复！';
	art.dialog.confirm(msg, function(){
		window.location.href = url;
	});
}

 function closebox(){

    if(boxobj!=null){
       
        boxobj.remove();
         boxobj=null;
    }
}
//日期输入格式化
$(function () {
try{
$("input.date").manhuaDate({
Event: "click", //可选    
Left: 0, //弹出时间停靠的左边位置
Top: -16, //弹出时间停靠的顶部边位置
fuhao: "-", //日期连接符默认为-
isTime: false, //是否开启时间值默认为false
beginY: 1949, //年份的开始默认为1949
endY: 2100//年份的结束默认为2049
});}catch(err){}


$("input.date").attr('readonly',true);

});


function addhtml(toobj,addobj){
   $html=addobj.html();
 // cdebug($html);
   toobj.append($html);
}
function delobj(obj){
   
    obj.remove();
    
}

function fastajax(obj,val,fun){
    
     $now=$(obj);
     $text=$now.val();
     if(val!=undefined){$text=val}//ajaxchoose时赋值
     if($text!=prevobjval){
     
     $name=$now.attr('name');
    
      $key=$now.attr('key');
    
     $url=location.href;
     $url=$url+'&t=ajax';
      $who=$now.attr('who');
     $v={'key':$key,'name':$name,'value':$text};
    //cdebug($who);
    if($who!=''){
        //cdebug($who);
      $v={'key':$key,'name':$name,'value':$text,'who':$who};
      }
      // cdebug($v);
     $val=makePost($v); 
   
     if(fun==undefined){fun=d;}
     yAjax($url,$val,fun);
     }else{
        prevobj.parent().html(prevobjval);
     }
}
 function trim(str,findstr){ 
        $word=findstr;
        if($word==undefined){
            $word='\\s*';
        }else{
           $word=($word); 
        }
        $find= new RegExp("(^"+$word+")|("+$word+"$)","g");
      
　　     return str.replace($find, "");
　　 }

function d(data){
    //cdebug(prevobj);
    if(data==1){
       //cdebug(prevobj.val());
       prevobj.parent().html(prevobj.val()); 
    }else{
        prevobj.parent().html(prevobjval);
    }
}
function cgstatus(data){
    //cdebug(prevobj);
    if(data==1){
        //cdebug(prevobj.val());
      $value= prevobj.children('div').attr('class');
          switch ($value) {
             case 'yes':prevobj.children('div').attr('class','no');break;
             case 'no':prevobj.children('div').attr('class','yes');break;
            
          } 
    }else{
        //prevobj.parent().html(prevobjval);
    }
}
//td加上clas为ajaxtext 可以快速上传
//必要参数key=主键，name=要修改的字段，
//可选参数who=当前操作所涉及的表
//保存原有的值

$(function(){
      $('td').mouseover(function(){
       $classname=$(this).attr('clas');
      
         $who='';
       if($classname!=undefined){
       switch ($classname) {
         case 'showimg':
         $src=$(this).text();
         $img='<img src="'+$src+'" style="width:150px"/>';
         //cdebug($(this).offset().left);
           msgbox('图片预览',$img,150,150,$(this).offset().left+50,$(this).offset().top);
          var t=setTimeout(" closebox();",5000)
           $(this).mouseout(function(){
            clearTimeout(t);
          closebox();
           }); 
         ;break;
         case '':
         
         
          
         
         
         
         ;break;
         
      }
     }
    });
    
    
    
    //////////////////////
    $('td').click(function(){
       $classname=$(this).attr('clas');
       $who='';
       if($classname!=undefined){
       switch ($classname) {
         case 'ajaxtext':
         if($(this).children('input').html()==undefined){
          prevobjval= $(this).text();
          //cdebug(prevobjval);
         $name=$(this).attr('name');
         $key=$(this).attr('key');
         $text=$(this).text();
         $find= new RegExp('\\|[\\s\\|\\-]*',"g");
         
        
         $text=$text.replace($find,'');
         
         $text=trim($text);
         $who=$(this).attr('who');
         $input='<input  style="z-index:999" type="text" name="'+$name+'" key="'+$key+'" value="'+$text+'" onblur="fastajax(this)" >';
         if($who!=''){
         $input='<input  style="z-index:999" type="text" name="'+$name+'" key="'+$key+'" value="'+$text+'" who="'+$who+'" onblur="fastajax(this)" >';  
         }        
         $(this).html($input);
         prevobj=$(this).children('input');
         $(this).children('input').focus();
         }else{
         // $(this).html(prevobjval);  
         }
         ;break;
         case 'ajaxchoose':
         
         if($(this).children('div').html()==undefined){
  
         }else{
           //cdebug('ggg');
          //$(this).html(prevobjval);
          //获取子div当前的状态
          prevobj=$(this);
          $child=$(this).children('div');
          $value= $child.attr('class');
          //yes本身是值1
          //no是值0
          
         //因为要改变状态，所以取反
          switch ($value) {
             case 'yes':$value=0;break;
             case 'no':$value=1;break;
             case 'stop':$value=2;//保留
             break;
          }
          
          fastajax(this,$value,cgstatus);
          
         }
         
         
         ;break;
         case '':;break;
      }
     }
    });
});
//回车事件
function keyenter()

{

    var k = window.event.keyCode;

   // var e = window.event.srcElement;

    if ( k == 13)

    {

     //   window.event.keyCode = 0;
//
//        window.event.returnValue = false;
      cdebug('回车了');

    }

}

function backprev(){
   history.back(-1);
}
$(function(){
    $('.back').click(function(){
        backprev();
    });
});





