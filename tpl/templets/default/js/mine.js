check_body_size();
// 设置加载几个AJAX
var load_number = 1;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}

initialization();
// 初始化
function initialization() {
    var check_token = localStorage.getItem('token');
    if (check_token && check_token != '') {
        // 获取用户余额
        get_user_yu_e();
        // 获取用户信息
        // get_user_mess();
        console.log("用户已登录");
        is_login();
        var check_login_type = localStorage.getItem('login_type');
        if (check_login_type == "facebook") {
            window.fbAsyncInit = function() {
                FB.init({
                    appId: '642299609555471',
                    xfbml: true,
                    version: 'v4.0'
                });
                FB.getLoginStatus(function(response) {
                    statusChangeCallback(response);
                });
            };
        }

    } else {
        // 添加登录按钮
        $('.user_page').addClass('go_login');
        check_load();
    }


}
// ——————————————————————————————————————————————————————————————————FB————————————————————————————————————————————
// Load the SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));



// 请求接口连接
var fbId, accessToken;

function statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
    // 是否登录
    if (response.status === 'connected') {
        // Logged into your app and Facebook.
        fbId = response.authResponse.userID;
        accessToken = response.authResponse.accessToken;
    } else {
        document.getElementById('status').innerHTML = 'Please log ' +
            'into this app.';
    }
}

function checkLoginState() {
    FB.getLoginStatus(function(response) {
        statusChangeCallback(response);
    });
}

function onSignIn(googleUser) {
    // Useful data for your client-side scripts:
    var profile = googleUser.getBasicProfile();
    console.log("ID: " + profile.getId()); // Don't send this directly to your server!
    console.log('Full Name: ' + profile.getName());
    console.log('Given Name: ' + profile.getGivenName());
    console.log('Family Name: ' + profile.getFamilyName());
    console.log("Image URL: " + profile.getImageUrl());
    console.log("Email: " + profile.getEmail());

    // The ID token you need to pass to your backend:
    var id_token = googleUser.getAuthResponse().id_token;
    console.log("ID Token: " + id_token);
}

function signOut() {
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function() {
        console.log('User signed out.');
        logout_clear();
    });
}



// ——————————————————————————————————————————————————————————————————FB————————————————————————————————————————————

$('.logout_button').on('click', function() {
    // 判断登录方式
    var check_login_type = localStorage.getItem('login_type');
    if (check_login_type == "facebook") {
        // alert("facebook");
        FB.logout(function(response) {
            logout_clear();
        });
    } else if (check_login_type == "google") {
        signOut();
    }
});



// 获取用户余额
function get_user_yu_e() {
    var data = get_ajax_header();
    $.ajax({
        type: "post",
        url: api_url + "user/remainder",
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

        },
        success: function(res) {
            console.log('获取用户余额 user/remainder');
            console.log(res);
            if (res.code == "1") {
                $('.yu_e').html(res.result);
            } else {
                console.log('获取用户余额 user/remainder 错误');
            }


            check_load();


        },
        error: function(e) {

            console.log('获取用户余额 user/remainder 请求失败');
            check_load();
        }
    });
}

// 获取用户信息
function get_user_mess() {


    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "common/get_user_mess",
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

        },
        success: function(res) {
            console.log('获取用户信息 common/get_user_mess');
            console.log(res);
            if (res.code == "1") {

            } else {
                console.log('获取用户信息 common/get_user_mess 错误');
            }


            check_load();


        },
        error: function(e) {

            console.log('获取用户信息 common/get_user_mess 请求失败');
            check_load();
        }
    });
}


// 已登录
function is_login() {
    // 关闭登录按钮
    $('.user_page').removeClass('go_login');
    // 隐藏关闭按钮
    $('.click_login').remove();
    // 显示用户信息
    $('.user_desc').show();
    // 设置头像
    var user_head = localStorage.getItem('avater');
    $('.user_head').attr("src", user_head);
    // 设置名字
    var user_name = localStorage.getItem('nickname');
    $('.user_name').html(user_name);
    // 设置ID
    var user_id = localStorage.getItem('uid');
    $('.user_id').html("ID:" + user_id);
    // 书券余额
    var golden_bean = localStorage.getItem('golden_bean');
    $('.yu_e').html(golden_bean);
    // 设置vip
    var vip = parseInt(localStorage.getItem('isvip'));
    if (vip == 1) {
        console.log("会员用户");
        $('.no_vip').hide();
        $('.is_vip').show();
    } else {
        console.log("非会员用户");
        $('.no_vip').show();
        $('.is_vip').hide();
    }
    // 显示退出按钮
    $('.logout_button').show();

    check_load();

}

$('.no_vip button').on('click', function() {
    location.href = 'download.html';
});