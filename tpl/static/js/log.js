function log($type){
    $url=vlogurl;
    $logid=vlogid;
    $.post($url,{logid:$logid,type:$type},function(){
        d('本次访问终止');
    });
    
    }


// window.onbeforeunload=function(){
    
// }


window.onbeforeunload=function (){
   
//     var   n   =   window.event.screenX   -   window.screenLeft;  
//     var   b   =   n   >   document.documentElement.scrollWidth-20;  
//     if(b   &&   window.event.clientY   <   0   ||   window.event.altKey)  
//     {  
//         log(2);
//         window.event.returnValue   =   "";     //这里可以放置你想做的操作代码  
//     }else{
//         log(1);
//    }  


    if(event.clientX>document.body.clientWidth && event.clientY < 0 || event.altKey){
        //  alert("你关闭了浏览器");
        log(2);
    }else{
        //  alert("你正在刷新页面");
        log(1);
    }
    }