function getadfunction(domain,$adurl,$id,$function){
  yAjax($adurl,{
      'id':$id
    },function(data){
      $obj=$('#'+$id);
      $html='';
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+data[i]['urlid']+'&adid='+data[i]['adid'];

        $html+='<li><a href="'+$url+'" target="_blank"><img style="height:'+data[i]['height']+'px;width:'+data[i]['width']+'px;" src="'+data[i]['pic']+'" border="0"></a></li>';
      }
      if($html){
        $obj.append($html);
      }
      $function($html);
    });
}
function getad(domain,$adurl,$id){
  yAjax($adurl,{
      'id':$id
    },function(data){
      $obj=$('#'+$id);
      $html='';

      var b = $.isEmptyObject(data);
      //判断是否空对象

      if(b){
        return false;
      }
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+data[i]['urlid']+'&adid='+data[i]['adid'];

        $html+='<a href="'+$url+'" target="_blank"><img style="height:'+data[i]['height']+'px;width:'+data[i]['width']+'px;" src="'+data[i]['pic']+'" border="0"></a>';
      }
      if($html){
        $obj.append($html);
      }

    });
}

function getad2(domain,$adurl,$id){
  yAjax($adurl,{
      'id':$id
    },function(data){
      $obj=$('#'+$id);
      $html='';
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+data[i]['urlid']+'&adid='+data[i]['adid'];

        $html+='<li><a href="'+$url+'" target="_blank"><img style="height:'+data[i]['height']+'px;width:'+data[i]['width']+'px;" src="'+data[i]['pic']+'" border="0"></a></li>';
      }
      if($html){
        $obj.append($html);
      }

    });
}
function getadmobile(domain,$adurl,$id){
  yAjax($adurl,{
      'id':$id
    },function(data){
      $obj=$('#'+$id);
      $html='';
      $page='';
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+data[i]['urlid']+'&adid='+data[i]['adid'];


        $html+='<div class="swiper-slide red-slide" style="width: '+data[i]['width']+'px; height: '+data[i]['height']+'px;"><div class="title"><a href="'+$url+'"><img class=" lazy-loading" src="'+data[i]['pic']+'" title="'+data[i]['alt']+'" style="width:100%"></a></div></div>';

      }
      if($html){

        $obj.append($html);

        var swiper=new Swiper('.swiper-container',{
            pagination:'.pagination',
            loop:true,
            grabCursor: true,
            autoplay: 1500,
            observer:true,
            observeParents:true,
            paginationClickable: true
          });
      }

    });
}
function getadmobile2(domain,$adurl,$class){
  yAjax($adurl,{
      'id':$class
    },function(data){
      $obj=$('.'+$class);
      $html='';
      $page='';
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+data[i]['urlid']+'&adid='+data[i]['adid'];

        $html+='<a href="'+$url+'" class="J_log item item-0" title="'+data[i]['alt']+'"><img class="lazy-loading" src="'+data[i]['pic']+'" title="'+data[i]['alt']+'"><p class="countdown"></p></a>';

      }
      if($html){

        $obj.append($html);

      }

    });
}
function getadmobile3(domain,$adurl,$class){
  yAjax($adurl,{
      'id':$class
    },function(data){
      $obj=$('.'+$class);
      $html='';
      $page='';
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+data[i]['urlid']+'&adid='+data[i]['adid'];

        $html+='<a href="'+$url+'" class="J_log item item-'+(i+1)+'" title="'+data[i]['alt']+'"><img class="lazy-loading" src="'+data[i]['pic']+'" title="'+data[i]['alt']+'"></a>';

      }
      if($html){

        $obj.append($html);

      }

    });
}
function getadmobilehot(domain,$adurl,$class){
  yAjax($adurl,{
      'id':$class
    },function(data){
      $obj=$('#'+$class);

      $html='';
      $page='';
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=product&a=detail'+'&productid='+data[i]['pid'];

        $html+='<li><a href="'+$url+'" class="img"><img src="'+data[i]['gimg']+'" alt="'+data[i]['gtitle']+'"></a><a class="title"><div class="text">'+data[i]['gtitle']+'</div></a><div class="price-wrapper"><span class="price"><span class="c_l c_coupon"></span><span class="c_r c_coupon"></span><b>'+data[i]['gprice']/100+'元</b></span></div></li>';

      }

      if($html){

        $obj.prepend($html);

      }

    });
}

function getadmobilead(domain,$adurl,$class){
  yAjax($adurl,{
      'id':$class
    },function(data){
      $obj=$('#'+$class);
      $html='';
      $page='';
      for (var i=0;i<data.length;i++)
      {
        $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+data[i]['urlid']+'&adid='+data[i]['adid'];

        $html+='<a href="'+$url+'"  title="'+data[i]['alt']+'"><img  src="'+data[i]['pic']+'" title="'+data[i]['alt']+'"></a>';

      }
      if($html){

        $obj.append($html);

      }



    });
}
function ads(domain,$adurl,$ids){

  yAjax($adurl,{
      'id':$ids
    },
    function(data){
      $.each(data,function($index,$val){
          $obj=$('#'+$index);
          $html='';
          var b = $.isEmptyObject($obj);
          if(!b){
            for (var i=0;i<$val.length;i++)
            {
              $url=domain+'/index.php?c=jump&a=ad'+'&urlid='+$val[i]['urlid']+'&adid='+$val[i]['adid'];
              $html+='<a href="'+$url+'" target="_blank"><img style="height:'+$val[i]['height']+'px;width:'+$val[i]['width']+'px;" src="'+$val[i]['pic']+'" border="0"></a>';
            }
            if($html){
            	
              $obj.append($html);
            
            }
          }

        });

    });
}