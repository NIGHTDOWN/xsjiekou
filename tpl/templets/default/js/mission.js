// 设置加载几个AJAX
var load_number = 1;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}

var running = true;

var isanswer;
var user_id = localStorage.getItem('uid');

userinfo();
get_signday();
// 获取用户信息
function userinfo() {



    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "user/userinfo",
        beforeSend: function(request) {
            request.setRequestHeader("apiKey", data.apiKey);
            request.setRequestHeader("timestamp", data.timestamp);
            request.setRequestHeader("token", data.token);
            request.setRequestHeader("deviceType", data.deviceType);
            request.setRequestHeader("viceos", data.viceos);
            request.setRequestHeader("devicetoken", data.devicetoken);
            request.setRequestHeader("uid", data.uid);
            request.setRequestHeader("apiSign", data.apiSign);
        },

        dataType: 'json',
        data: {
            "user_id": user_id
        },
        success: function(res) {
            console.log('获取用户信息 user/userinfo');
            console.log(res);
            if (res.code == "1") {
                isanswer = res.result.isanswer;
            } else {
                console.log('获取用户信息 user/userinfo 错误');
            }


            check_load();


        },
        error: function(e) {

            console.log('获取用户信息 user/userinfo 请求失败');
            check_load();
        }
    });
}
// 签到天数
function get_signday() {



    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "user/get_signday",
        beforeSend: function(request) {
            request.setRequestHeader("apiKey", data.apiKey);
            request.setRequestHeader("timestamp", data.timestamp);
            request.setRequestHeader("token", data.token);
            request.setRequestHeader("deviceType", data.deviceType);
            request.setRequestHeader("viceos", data.viceos);
            request.setRequestHeader("devicetoken", data.devicetoken);
            request.setRequestHeader("uid", data.uid);
            request.setRequestHeader("apiSign", data.apiSign);
        },

        dataType: 'json',
        data: {
            "user_id": user_id
        },
        success: function(res) {
            console.log('签到天数 get_signday');
            console.log(res);
            if (res.code == "1") {
                var length = res.result.num;
                if (length > 7) {
                    length = 7;
                }

                for (var i = 0; i < length; i++) {
                    $('.lists').eq(i).addClass("qiandao_lists")
                }
            } else {
                console.log('签到天数 get_signday 错误');
            }


            check_load();


        },
        error: function(e) {

            console.log('签到天数 get_signday 请求失败');
            check_load();
        }
    });
}




$('.mission_lists button').on('click', function() {

    if (check_token) {
        location.href = 'question.html?uid=' + user_id;
    } else {
        go_login();
    }
});

$('.qiandao_button').on('click', function() {
    if (running) {
        running != running;
        if (check_token) {
            sign();
        } else {
            running != running;
            go_login();
        }


    }
});


// 签到
function sign() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "user/sign",
        beforeSend: function(request) {
            request.setRequestHeader("apiKey", data.apiKey);
            request.setRequestHeader("timestamp", data.timestamp);
            request.setRequestHeader("token", data.token);
            request.setRequestHeader("deviceType", data.deviceType);
            request.setRequestHeader("viceos", data.viceos);
            request.setRequestHeader("devicetoken", data.devicetoken);
            request.setRequestHeader("uid", data.uid);
            request.setRequestHeader("apiSign", data.apiSign);
        },

        dataType: 'json',
        data: {
            "user_id": user_id
        },
        success: function(res) {
            console.log('签到 user/sign');
            console.log(res);
            if (res.code == "1") {
                showToast({
                    text: 'ลงชื่อสำเร็จ', //【必填】，否则不能正常显示 , 剩余的其他不是必填
                    top: '45%', //toast距离页面底部的距离
                    zindex: 50, //为了防止被其他控件遮盖，z-index默认为2
                    speed: 500, //toast的显示速度
                    time: 2000, //toast显示多久以后消失
                    img: 'yes'
                });
                get_signday();
            } else {
                console.log('签到 user/sign 错误');
                check_code(res.code);
            }

            running != running;
            check_load();


        },
        error: function(e) {

            console.log('签到 user/sign 请求失败');
            check_load();
            running != running;
        }
    });
}