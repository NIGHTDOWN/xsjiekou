check_body_size();

// 设置加载几个AJAX
var load_number = 2;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}


var book_id = GetQueryString("book_id");
var cartoon_id = GetQueryString("cartoon_id");

// 记录id
var wid;
var sectionid = GetQueryString("section_id");
// 记录纠错类型
var titletype = '';
// 记录是小说还是漫画
var book_type;





check_book_type();
// 检查书本还是漫画
function check_book_type() {
    if (book_id) {
        book_type = 0;
        wid = book_id;
    } else if (cartoon_id) {
        book_type = 1;
        wid = cartoon_id;
    }
    check_load();
}

get_wrongtype();
// 获取纠错类型
function get_wrongtype() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "common/get_wrongtype",
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
        data: {},
        success: function(res) {
            console.log('获取纠错类型 common/get_wrongtype');
            console.log(res);
            if (res.code == "1") {
                var html = '';
                for (var i = 0; i < res.result.length; i++) {
                    html += `<li class="lists cursor flexCenter-ai-center cursor" data-titletype="` + i + `">
                    <div class="img_"></div>
                    <p>` + res.result[i] + `</p>
                </li>`;
                }
                $('.list li').remove();
                $('.list').append(html);

            } else {
                console.log('获取纠错类型common/get_wrongtype 错误');
            }

            check_load();

        },
        error: function(e) {
            console.log('获取纠错类型common/get_wrongtype 错误');
            check_load();

        }
    });
}

// 选择纠错类型
$('body').on('click', '.lists', function() {
    if ($(this).hasClass('isClick')) {
        $(this).removeClass('isClick');
        $(this).siblings().removeClass('isClick');
        titletype = '';
    } else {
        $(this).addClass('isClick');
        $(this).siblings().removeClass('isClick');
        titletype = $(this).data("titletype");
    }
});

// 反馈
$('.discuss_button').on('click', function() {


    titletype = $('.isClick').data('titletype');
    if (!titletype) {
        showToast({
            text: 'โปรดเลือกประเภทที่ไม่ถูกต้อง', //【必填】，否则不能正常显示 , 剩余的其他不是必填
            top: '45%', //toast 距离页面底部的距离
            zindex: 999, //为了防止被其他控件遮盖，z-index默认为2
            speed: 500, //toast的显示速度
            time: 2000, //toast显示多久以后消失
            img: 'no'
        });
        return false;
    }


    // 内容
    var content = $('.discuss_textarea').val();
    if (!content) {
        showToast({
            text: 'โปรดกรอกข้อมุล', //【必填】，否则不能正常显示 , 剩余的其他不是必填
            top: '45%', //toast 距离页面底部的距离
            zindex: 999, //为了防止被其他控件遮盖，z-index默认为2
            speed: 500, //toast的显示速度
            time: 2000, //toast显示多久以后消失
            img: 'no'
        });
        return false;
    }




    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "user/correction",
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
            "type": book_type,
            "wid": wid,
            "sectionid": sectionid,
            "titletype": titletype,
            "content": content

        },
        success: function(res) {
            console.log('纠错 user/correction');
            if (res.code == "1") {
                showAlert({
                    text: 'ส่งสำเร็จ', //【必填】，否则不能正常显示
                    btnText: 'ยืนยัน', //按钮的文本
                    top: '34%', //alert弹出框距离页面顶部的距离
                    zindex: 999, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1，修改后黑色遮盖层的z-index是这个数值的-1
                    color: '#fff', //按钮的文本颜色，默认白色
                    bgColor: '#ffa200', //按钮的背景颜色，默认为#1b79f8
                    success: function() { //点击按钮后的回调函数
                        goBack();
                    }
                });
            } else {
                console.log('添加小说评论 user/add_agree 错误');
                check_code(res.code);
            }



        },
        error: function(e) {
            console.log('添加小说评论 user/add_agree 请求失败');

        }
    });


});