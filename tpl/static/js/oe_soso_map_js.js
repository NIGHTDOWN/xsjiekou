
var geocoder,map,marker,center,mapinfo= null;
var mapaddress,ico=null;
var init_mark_move=function(){};
var init_mark_click=function(){};
var initmap = function ()
{
   
ico= new qq.maps.MarkerImage('http://open.map.qq.com/doc/img/nilt.png');
  center = new qq.maps.LatLng(23.125178,113.280637);
	mapobj=document.getElementById('myMap');
	option={
	 'center': center,
	 'zoom':15
	};
    map = new qq.maps.Map(mapobj,option);
    geocoder = new qq.maps.Geocoder({
	    'error':function(){cdebug('出错了，请输入正确的地址！！！');},
        'complete' : function(result){
                                        mapaddress=result.detail.address;
									    map.setCenter(result.detail.location);
								     	initmark(result.detail.location);
                                        //marker = new qq.maps.Marker({'position': p,'map': map});
                                       }
    });
}
function initcity(address) {
  
    if(address!=''){
     geocoder.getLocation(address);
 }
    
}
function saddress(address) {
 if(address!=''){
     geocoder.getLocation(address);
 }
   
   
	 
}
function initmark(p){
  
  marker = new qq.maps.Marker({
                //设置Marker的位置坐标
               'position': p,
                //设置显示Marker的地图
                'map': map,
                //可点击
               'clickable':true,
                //可以拖动
              'draggable':true,
                //设置图标
              // 'icon':ico,
                //设置标题
               // 'title':'oemarry地图api',
               'animation':qq.maps.MarkerAnimation.DOWN
            });
		mapinfo = new qq.maps.InfoWindow({
                map: map
            });	
         qq.maps.event.addListener(marker, 'click', init_mark_click); 	
          qq.maps.event.addListener(marker, 'dragend', init_mark_move); 
}
function showmapinfo(){
    mapinfo.open();
    mapinfo.setContent('<div style="text-align:center;white-space:nowrap;' +
                    'margin:10px;">当前的坐标为:'+marker.getPosition()+'</div><div style="float:right">点击该点获取坐标</div>');
                mapinfo.setPosition(marker.getPosition());
}
function map_click(oe_funname){
 
      
       
       init_mark_click=oe_funname;
    
}
function map_move(){
    
       init_mark_move=showmapinfo;
    
}