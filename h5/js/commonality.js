$('html').css('font-size', document.body.clientWidth / 7.5 + 'px');
window.addEventListener('resize', function() {
    $('html').css('font-size', document.body.clientWidth / 7.5 + 'px');
})

// alert(111);

//获取参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

// 检查body高度设置padding-bottom
function check_body_size() {
    var body_height = window.innerHeight;
    if (body_height >= 800) {
        $('body').css({
            "padding-bottom": "1.54rem",
        });

    } else {
        $('body').css({
            "padding-bottom": "1.2rem",
        });
    }

    window.addEventListener('resize', function() {
        $('html').css('font-size', document.body.clientWidth / 7.5 + 'px');
        // console.log(window.innerHeight);
        var body_height = window.innerHeight;
        if (body_height >= 800) {
            $('body').css({
                "padding-bottom": "1.54rem",
            });

        } else {
            $('body').css({
                "padding-bottom": "1.2rem",
            });
        }
    })
}


// 下方下载提示高度设置
bottom_download_size();

function bottom_download_size() {
    var body_height = window.innerHeight;
    if (body_height >= 800) {
        $('.bottom_download').css({
            "padding-bottom": '0.5rem'
        });
        $('.font_icon_page').css({
            "bottom": '1.2rem'
        });

    } else {
        $('.bottom_download').css({
            "padding-bottom": '0.2rem'
        });
        $('.font_icon_page').css({
            "bottom": '0.8rem'
        });
    }
    window.addEventListener('resize', function() {
        $('html').css('font-size', document.body.clientWidth / 7.5 + 'px');
        // console.log(window.innerHeight);
        var body_height = window.innerHeight;
        if (body_height >= 800) {
            $('.bottom_download').css({
                "padding-bottom": '0.5rem'
            });
            $('.font_icon_page').css({
                "bottom": '1.2rem'
            });

        } else {
            $('.bottom_download').css({
                "padding-bottom": '0.2rem'
            });
            $('.font_icon_page').css({
                "bottom": '0.8rem'
            });
        }
    })
}





// _________________________________基本配置_______________________________
// 接口地址
var api_url = "/api/",
    // 接口密钥
    apiKey = "d621b33de3cfa050c7bb8614d6ad50ea",
    // 接口密钥
    apiSecret = "8a8b79104e3a3695c8b0e06db8a9e5b0",
    // 
    viceos = '1.0.0',
    // 设备类型（android，ios，wap）
    deviceType = 'wap',
    // 设备唯一识别码    
    devicetoken = "",
    window_url = "/h5/";

// localStorage.setItem('token', 'sadsda0');
var check_token = localStorage.getItem("token");
// 深度连接 ios_section_id
var ios_section_id = '';


// get_ajax_header()
// 封装接口加密
function get_ajax_header() {
    var header_data = {};
    header_data.apiKey = apiKey;
    header_data.timestamp = Date.parse(new Date()) / 1000;
    header_data.token = localStorage.getItem('token');
    header_data.deviceType = deviceType;
    header_data.viceos = viceos;
    header_data.devicetoken = devicetoken;
    var key_arr = [];
    for (var k in header_data) {
        key_arr.push(k);
    }
    key_arr = key_arr.sort();
    var hash_data = '';
    for (var i = 0; i < key_arr.length; i++) {
        hash_data += encodeURI(header_data[key_arr[i]]);
    }
    var apiSign = hex_hmac_md5(apiSecret, hash_data);
    header_data.apiSign = apiSign;
    header_data.uid = localStorage.getItem('uid');
    // console.log(header_data);
    return header_data;
}




// 去小说页面
$('body').on('click', '.go_fiction', function(event) {
    var number = $(this).data("book_id");
    window.location.href = "fictionDetailPage.html?book_id=" + number;
});
// 去漫画页面
$('body').on('click', '.go_cartoon', function(event) {
    var number = $(this).data("cartoon_id");
    window.location.href = "cartoonDetailPage.html?cartoon_id=" + number;
});

// 跳转类型
// 1网页、2小说、3漫画、4充值 5 网页充值recharge
$('body').on("click", '.check_goal_type', function() {
    var goal_type = $(this).data("goal_type");
    if (goal_type == "1") {
        var html_url = $(this).data("banner_url");
        window.location.href = html_url;
    } else if (goal_type == "2") {
        var number = $(this).data("book_id");
        window.location.href = "fictionDetailPage.html?book_id=" + number;
    } else if (goal_type == "3") {
        var number = $(this).data("cartoon_id");
        window.location.href = "cartoonDetailPage.html?cartoon_id=" + number;
    } else if (goal_type == "4") {
        window.location.href = "recharge.html";
    } else if (goal_type == "5") {
        window.location.href = "recharge.html";
    }
});




// 返回上一页
$('body').on('click', '.back_button', function(event) {
    goBack();
});

function goBack() {
    if ((navigator.userAgent.indexOf('MSIE') >= 0) && (navigator.userAgent.indexOf('Opera') < 0)) { // IE
        if (history.length > 0) {
            window.history.back(-1);
        } else {
            window.location.href = "index.html";
        }
    } else { //非IE浏览器
        if (navigator.userAgent.indexOf('Firefox') >= 0 ||
            navigator.userAgent.indexOf('Opera') >= 0 ||
            navigator.userAgent.indexOf('Safari') >= 0 ||
            navigator.userAgent.indexOf('Chrome') >= 0 ||
            navigator.userAgent.indexOf('WebKit') >= 0) {

            if (window.history.length > 1) {
                window.history.back(-1);
            } else {
                window.location.href = "index.html";
            }
        } else { //未知的浏览器
            window.history.back(-1);
        }
    }
}
// 加载结束
function load_end() {
    $('.load-mask').fadeOut();
}




// 去搜索页面
$('.nav_search_button').on('click', function() {
    window.location.href = 'search.html';
});

// 登出清除数据
function logout_clear() {
    // 设置头像
    localStorage.setItem('avater', '');
    // 设置名字
    localStorage.setItem('nickname', '');
    // 设置vip
    localStorage.setItem('isvip', '');
    // 设置token
    localStorage.setItem('token', '');
    // 设置openid
    localStorage.setItem('openid', '');
    // 设置uid
    localStorage.setItem('uid', '');
    // 设置登录类型
    localStorage.setItem('login_type', '');
    // 是否新用户
    localStorage.setItem('isnew', '');
    // 金豆余额
    localStorage.setItem('remainder', '');
    // 书券余额
    localStorage.setItem('golden_bean', '');
    location.reload();
}

// 去登录
$('.go_login').on('click', function() {
    go_login();
});


// 进入书架
$('.nav_bookrack_button').on('click', function() {
    if (check_token) {
        location.href = "bookrack.html";
    } else {
        go_login();
    }
});



// 检查错误代码
function check_code(code) {
    switch (code) {
        case "100128":
            // 已经加入了书架
            code100128();
            break;
        case "100111":
            // 余额不足
            code100111();
            break;
        case "100110":
            // 用户未登录
            go_login();

            break;
        case "100112":
            // 签到过了
            code100112()

            break;
        case "100155":
            // 签到过了
            code100155()

            break;


        default:
            showAlert({
                text: '', //【必填】，否则不能正常显示
                btnText: 'เติมเงิน', //按钮的文本
                top: '34%', //alert弹出框距离页面顶部的距离
                zindex: 99, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1，修改后黑色遮盖层的z-index是这个数值的-1
                color: '#fff', //按钮的文本颜色，默认白色
                bgColor: '#ff5026', //按钮的背景颜色，默认为#1b79f8
                success: function() { //点击按钮后的回调函数
                    goBack();
                }
            });
    }
}


// 余额不足
function code100111() {
    console.log("余额不足");
    showAlert({
        text: 'ยอดเงินไม่เพียงพอ โปรดเติมเงิน', //【必填】，否则不能正常显示
        btnText: 'เติมเงิน', //按钮的文本
        top: '34%', //alert弹出框距离页面顶部的距离
        zindex: 99, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1，修改后黑色遮盖层的z-index是这个数值的-1
        color: '#fff', //按钮的文本颜色，默认白色
        bgColor: '#ff5026', //按钮的背景颜色，默认为#1b79f8
        success: function() { //点击按钮后的回调函数
            // location.href = 'login.html';
        }
    });
}
// 去登录页面
function go_login() {
    console.log("用户未登录");
    showAlert({
        text: 'ยังไม่ได้เข้าสู่ระบบ กรุณาเข้าสู่ระบบก่อน', //【必填】，否则不能正常显示
        btnText: 'เข้าสู่ระบบ', //按钮的文本
        top: '34%', //alert弹出框距离页面顶部的距离
        zindex: 99, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1，修改后黑色遮盖层的z-index是这个数值的-1
        color: '#fff', //按钮的文本颜色，默认白色
        bgColor: '#ff5026', //按钮的背景颜色，默认为#1b79f8
        success: function() { //点击按钮后的回调函数
            location.href = 'login.html';
        }
    });
}
// 已经加入了书架
function code100128() {
    console.log("code=100128");
    showToast({
        text: 'มีอยู่ในคลังแล้ว',
        top: '45%',
        zindex: 9999,
        speed: 500,
        time: 3000,
        img: 'no'
    });
}
// 已经签到过了
function code100112() {
    console.log("code=100112");
    showToast({
        text: 'วันนี้ลงชื่อไปแล้ว',
        top: '45%',
        zindex: 9999,
        speed: 500,
        time: 3000,
        img: 'no'
    });
}
// 评论失败
function code100155() {
    console.log("code=100155");
    showToast({
        text: 'ส่งความคิดเห็นล้มเหลว โปรดติดต่อแอดมิน',
        top: '45%',
        zindex: 9999,
        speed: 500,
        time: 3000,
        img: 'no'
    });
}


// 联系客服
function kf_online() {
    showAlert({
        text: 'การโหลดล้มเหลว', //【必填】，否则不能正常显示
        btnText: 'ติดต่อแอดมินออนไลน์', //按钮的文本
        top: '34%', //alert弹出框距离页面顶部的距离
        zindex: 99, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1，修改后黑色遮盖层的z-index是这个数值的-1
        color: '#fff', //按钮的文本颜色，默认白色
        bgColor: '#ff5026', //按钮的背景颜色，默认为#1b79f8
        success: function() { //点击按钮后的回调函数
            location.href = '';
            // location.reload();
        }
    });
}
// 点下载按钮
$('.bottom_download button').on('click', function() {
    check_phone_download();

});
// 去下载
function go_download() {
    location.href = 'https://apiv1.aikoversea.com/index/down/run?uid=1';
}

// 检查手机型号
function check_phone_download() {
    var u = navigator.userAgent,
        app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; // Android
    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // ios
    if (isAndroid) {
        go_download();
    } else if (isIOS) {
        $.post("/index/down/downiphone", {
                users_id: "1",
                type: 2
            },
            function(data) {
                if (data.code == "1") {
                    // __________________________________________________
                    var ios_bookid = '';
                    var ios_type = GetQueryString("type") ? GetQueryString("type") : "";
                    // alert(ios_type);
                    var ios_uid = '';
                    var ios_cn = '';
                    // 打开类型 1 详情页，2是阅读页
                    var ios_subtype = '';

                    // 是否分享页面进来的
                    if (ios_type) {
                        // alert("分享页面")
                        ios_subtype = 2;
                        // 设置深度连接uid
                        ios_uid = GetQueryString("uid");
                        // 设置深度连接 渠道cn
                        ios_cn = GetQueryString('cn');
                        // alert("now_number" + now_number)
                        ios_section_id = now_number - 1;

                        // 分享页面进来的
                        if (ios_type == "1") {
                            // ios_type类型  小说



                            // 设置深度连接ios_bookid
                            ios_bookid = GetQueryString("bid");




                        } else if (ios_type == "2") {
                            // ios_type类型  卡通


                            // 设置深度连接ios_bookid
                            ios_bookid = GetQueryString("cid");


                        }






                    } else {
                        // web端打开
                        // alert("web页面")


                        // ios_type 小说1 漫画2
                        var check_ios_type_book = GetQueryString("book_id");
                        var check_ios_type_cartoon = GetQueryString("cartoon_id");
                        if (check_ios_type_book) {
                            ios_type = 1;
                            ios_subtype = 1;
                            ios_bookid = GetQueryString("book_id");
                            // alert("这是小说")
                        } else if (check_ios_type_cartoon) {
                            ios_type = 2;
                            ios_subtype = 1;
                            ios_bookid = GetQueryString("cartoon_id");
                            // alert("这是漫画")
                        } else {

                        }
                        // 检查详情页




                        // 查看页面类型
                        var check_section_id = GetQueryString("section_id");
                        if (check_section_id) {
                            // alert("这是阅读页")
                            // 阅读页
                            ios_subtype = 2;
                            ios_section_id = now_number - 1;
                        } else {

                        }
                    }


                    window.location.href = "https://yxbookread.page.link/?link=https://apiv1.aikoversea.com/h5/share.html?params=type:" + ios_type + "-bookid:" + ios_bookid + "-uid:" + ios_uid + "-cn:" + ios_cn + "-sid:" + ios_section_id + "-subtype:" + ios_subtype + "&apn=com.example.android&isi=1484770042&ibi=com.jdljf.net&efr=1";
                } else {
                    window.location.reload();
                }
            },
            "json");



    } else {
        go_download();
    }
}