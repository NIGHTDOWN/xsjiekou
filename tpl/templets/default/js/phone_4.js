function is_phone() {
    var sUserAgent = navigator.userAgent.toLowerCase();
    var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
    var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
    var bIsMidp = sUserAgent.match(/midp/i) == "midp";
    var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
    var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
    var bIsAndroid = sUserAgent.match(/android/i) == "android";
    var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
    var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
    // document.writeln("您的浏览设备为：");
    if (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) {
        console.log("移动");
        return true;
    } else {
        console.log("PC");
        return false;
    };
}

if (is_phone()) {

    //移动端，引入移动端文件
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    if (isAndroid) {
        $('.download-box #downloadBtn-android').show();

    }
    if (isIOS) {
        $('.download-box #downloadBtn-iosSign').show();
    }

    $('.desktop').remove();

    var e = document.documentElement || document.body,
        a = "orientationchange" in window ? "orientationchange" : "resize",
        b = function () {
            var f = e.clientWidth;
            // e.style.fontSize = (f >= 750) ? "100px" : 100 * (f / 750) + "px"
            e.style.fontSize = (f >= 580) ? "77px" : 77 * (f / 580) + "px"
        };
    b();
    $('#fullpage').fullpage({
        autoScrolling: true,
        scrollHorizontally: true,
        navigation: true,
    });
} else {

    // $('body').css({
    //     'width':'100%',
    //     'max-width':'100%!important'
    // })
    //pc
    removeCss('mobile.css');
    removeCss('doc.css');
    removeCss('reset.css');

    removeScript('mobile.js');
    removeScript('index.js')

    loadCss('/tpl/templets/default/css/desktop.css');

    $('.m_main').remove();

    $('.fp-enabled').css({
        'font-size': ''
    });


    var subjectList = document.querySelectorAll('.subject img');
    $('#fullpage').fullpage({
        autoScrolling: true,
        scrollHorizontally: true,
        navigation: true,
        // navigationPosition: 'right',//导航小圆点的位置
        onLeave: function (org, det) {
            subjectList.forEach(function (item, index) {
                if (det.index === index) {
                    item.style.opacity = 1
                } else {
                    item.style.opacity = 0
                }
            })
        }
    });
    // new fullpage('#fullpage', {
    //     autoScrolling:true,
    //     scrollHorizontally: true,
    //     navigation:true,
    //     onLeave:function (org,det) {
    //         subjectList.forEach(function (item,index) {
    //             if(det.index === index){
    //                 item.style.opacity = 1
    //             }else{
    //                 item.style.opacity = 0
    //             }
    //         })
    //     }
    // });

    new QRCode(document.getElementById("qrcode1"), {
        text: location.href,
        width: 300,
        height: 300,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.Q
    });



}



function removeCss(href) {
    var links = document.getElementsByTagName("link");
    for (var i = 0; i < links.length; i++) {
        var _href = links[i].href;
        if (links[i] && links[i].href && links[i].href.indexOf(href) != -1) {
            links[i].parentNode.removeChild(links[i]);
        }
    }
}

function loadScript(src) {
    var addSign = true;
    var scripts = document.getElementsByTagName("script");
    for (var i = 0; i < scripts.length; i++) {
        if (scripts[i] && scripts[i].src && scripts[i].src.indexOf(src) != -1) {
            addSign = false;
        }
    }
    if (addSign) {
        var $script = document.createElement('script');
        $script.setAttribute("type", "text/javascript");
        $script.setAttribute("src", src);
        document.getElementsByTagName("head").item(0).appendChild($script);
    }
}

function loadCss(href) {
    var addSign = true;
    var links = document.getElementsByTagName("link");
    for (var i = 0; i < links.length; i++) {
        if (links[i] && links[i].href && links[i].href.indexOf(href) != -1) {
            addSign = false;
        }
    }
    if (addSign) {
        var $link = document.createElement("link");
        $link.setAttribute("rel", "stylesheet");
        $link.setAttribute("type", "text/css");
        $link.setAttribute("href", href);
        document.getElementsByTagName("head").item(0).appendChild($link);
    }
}

function removeScript(src) {
    var scripts = document.getElementsByTagName("script");
    for (var i = 0; i < scripts.length; i++) {
        if (scripts[i] && scripts[i].src && scripts[i].src.indexOf(src) != -1) {
            scripts[i].parentNode.removeChild(scripts[i]);
        }
    }
}