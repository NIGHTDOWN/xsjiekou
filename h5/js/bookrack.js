var deling = false;

// 设置加载几个AJAX
var load_number = 3;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}



initialization();
// 初始化
function initialization() {
    // 获取推荐小说
    get_hotw_groom();
    // 获取书架信息
    get_rack();
    // 你可能喜欢的
    get_hot_groom();

}

// 获取推荐小说
function get_hotw_groom() {
    var data = get_ajax_header();
    $.ajax({
        type: "post",
        url: api_url + "book/hotw_groom",
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
            console.log('获取推荐小说 book/hotw_groom');
            console.log(res);
            if (res.code == "1") {
                // 清除渲染
                $('.recommend_list').remove();
                var html = '';
                var result = res.result;
                for (var i = 0; i < 1; i++) {
                    html = `<div class="recommend_list flex-wrap-nowrap  go_fiction" data-book_id='` + result[i]["book_id"] +
                        `'>
                <img src="` + result[i]["bpic"] + `" class="recommend_cover_img" onerror="this.src='images/replace_2.png'">
                <div class="max_5">
                    <div class="flex-wrap-nowrap">
                        <p class="book_name p1-hidden">` + result[i]["other_name"] + `</p>
                        <img src="images/Fill@2x.png" class="hot_img">
                    </div>
                    <p class="recommend_desc p2-hidden">
                        ` + result[i]["desc"] + `
                    </p>
                </div>
            </div>`;
                }
                // 渲染
                $('.recommend_row').append(html);

            } else {
                console.log('获取推荐小说book/hotw_groom 错误');
            }


            check_load();

        },
        error: function(e) {
            check_load();
            console.log('获取推荐小说 book/hotw_groom请求失败');
        }
    });
}

// 获取书架信息
function get_rack() {
    var data = get_ajax_header();
    $.ajax({
        type: "post",
        url: api_url + "groom/get_rack",
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
            console.log('获取书架信息 book/hotw_groom');
            console.log(res);
            if (res.code == "1") {
                // 清除渲染
                $('.bookrack_list li').remove();
                var html = '';
                var result = res.result;
                for (var i = 0; i < result.length; i++) {

                    html += `<li>
                                <div class="bookrack_lists flexCenter-ai-center flexCenter-jc-sb" data-type="` + result[i]["type"] + `"  data-book_id="` + result[i]["book_id"] + `">
                                    <img src="` + result[i]["bpic"] + `" alt="" class="bookrack_lists_cover_img cursor" onerror="this.src='images/replace_2.png'">
                                    <div class="the_book_detail">
                                        <p class="p1-hidden cursor">` + result[i]["other_name"] + `</p>
                                        <p class="p1-hidden cursor">` + result[i]["desc"] + `</p>
                                    </div>
                                    <div>
                                        <div class="the_book_read">
                                            <div class=" flex-wrap-nowrap flexCenter-ai-center cursor">
                                                <span>อ่านต่อ</span>
                                                <img src="images/go_right@2x.png">
                                            </div>
                                        </div>
                                        <div class="the_book_del">
                                            <div class="w-100 h-100">
                                                <img src="images/choose_unclick@2x.png" class="cursor del_img">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>`;
                }
                // 渲染
                $('.bookrack_list').append(html);

            } else {
                console.log('获取书架信息book/hotw_groom 错误');
            }
            check_load();

        },
        error: function(e) {
            check_load();
            console.log('获取书架信息book/hotw_groom 请求失败');
        }
    });
}


// 你可能喜欢的
function get_hot_groom() {
    var data = get_ajax_header();
    $.ajax({
        type: "post",
        url: api_url + "book/hot_groom",
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
            console.log('你可能喜欢的 book/hot_groom');
            console.log(res);
            if (res.code == "1") {
                // 清除渲染
                $('.youlike ul li').remove();
                var html = '';
                var result = res.result;
                var result_length;
                if (result.length > 3) {
                    result_length = 3;
                } else {
                    result_length = result.length;
                }
                for (var i = 0; i < result_length; i++) {
                    html += `<li>
                                <div class="youlike_lists cursor flexCenter-ai-center go_fiction" data-book_id="` + result[i]["book_id"] + `">
                                    <img src="` + result[i]["bpic"] + `" onerror="this.src='images/replace_2.png'"" class="youlike_lists_cover_img cursor">
                                    <div class="youlike_the_book_detail max_5 cursor">
                                        <p class="p1-hidden">` + result[i]["other_name"] + `</p>
                                    </div>
                                </div>
                            </li>`;
                }
                // 渲染
                $('.youlike ul').append(html);

            } else {
                console.log('你可能喜欢的book/hot_groom 错误');
            }


            check_load();

        },
        error: function(e) {
            check_load();
            console.log('你可能喜欢的book/hot_groom 请求失败');
        }
    });
}




// 取消编辑
$('.bianji_close').on('click', function() {
    // 上方导航
    $('.nav_button').show();
    // 编辑完成按钮
    $('.bianji_close').hide();
    // 推荐列表
    $('.recommend_row').show();
    // 设置按钮
    $('.the_book_read ').show();
    $('.the_book_del').hide();

    $('.add_youlike').show();
    $('.youlike').show();
    $('.youlike_title').show();
    $('.bottom_del_nav').hide();
    var length = $('.bookrack_lists').length;
    for (var i = 0; i < length; i++) {

        $('.bookrack_lists').eq(i).removeClass('bookLists_del');
        $('.bookrack_lists').eq(i).find('.del_img').attr("src", "images/choose_unclick@2x.png");
    }
    deling = !deling;
});
// 点击编辑按钮
$('.bianji_button').on('click', function() {
    if (!check_token) {
        go_login();
        return false;
    }
    // 上方导航
    $('.nav_button').hide();
    // 编辑完成按钮
    $('.bianji_close').show();
    // 推荐列表
    $('.recommend_row').hide();
    // 设置按钮
    $('.the_book_read ').hide();
    $('.the_book_del').show();

    $('.add_youlike').hide();
    $('.youlike_title').hide();
    $('.youlike').hide();
    $('.bottom_del_nav').show();
    deling = !deling;
});


// 点击书本
$('body').on('click', '.bookrack_lists', function(event) {
    console.log(deling);
    // 编辑中
    if (deling) {
        // 是不是选中del
        if ($(this).hasClass('bookLists_del')) {
            if ($(this).data('thetype') == "1") {
                $(this).removeClass('fiction_del');

            } else if ($(this).data('thetype') == "2") {
                $(this).removeClass('cartoon_del');
            }
            $(this).removeClass('bookLists_del');
            $(this).find('.del_img').attr("src", "images/choose_unclick@2x.png");
        } else {
            if ($(this).data('thetype') == "1") {
                $(this).addClass('fiction_del');

            } else if ($(this).data('thetype') == "2") {
                $(this).addClass('cartoon_del');
            }
            $(this).addClass('bookLists_del');
            $(this).find('.del_img').attr("src", "images/choose_click@2x.png");
        }
        check_del_length();
    } else {
        // 不是编辑中
        var type = $(this).data("type");
        var data_book_id = $(this).data("book_id");
        if (type == "1") {
            location.href = 'fictionDetailPage.html?book_id=' + data_book_id;
        } else if (type == "2") {
            location.href = 'cartoonDetailPage.html?cartoon_id=' + data_book_id;
        }
    }
});


// 全选
$('.pitch_button').on('click', function() {
    console.log(1);
    var length = $('.bookrack_lists').length;
    for (var i = 0; i < length; i++) {

        if ($('.bookrack_lists').eq(i).data('thetype') == "1") {
            $('.bookrack_lists').eq(i).addClass('fiction_del');

        } else if ($('.bookrack_lists').eq(i).data('thetype') == "2") {
            $('.bookrack_lists').eq(i).addClass('cartoon_del');
        }

        $('.bookrack_lists').eq(i).addClass('bookLists_del');
        $('.bookrack_lists').eq(i).find('.del_img').attr("src", "images/choose_click@2x.png");
    }
    check_del_length();
});
// 计算长度
function check_del_length() {
    var length = $('.bookLists_del').length;
    if (length > 0) {
        $('.del_button').addClass('bookRack_delButton_on');
        // $('.del_button').html("删除(" + length + ")");
        $('.del_button').html("ลบ(" + length + ")");
    } else {
        $('.del_button').removeClass('bookRack_delButton_on');
        // $('.del_button').html("删除(0)");
        $('.del_button').html("ลบ(0)");
    }

}


// 删除
$('.del_button').on('click', function() {
    if ($(this).hasClass('bookRack_delButton_on') && $('.bookLists_del').length > 0) {
        showConfirm({
            text: 'ลบหนังสือที่เลือกหรือไม่?', //【必填】，否则不能正常显示
            rightText: 'ยืนยัน', //右边按钮的文本
            rightBgColor: '#15C5B8', //右边按钮的背景颜色，【不能设置为白色背景】
            rightColor: '#fff', //右边按钮的文本颜色，默认白色
            // leftText: '取消', //左边按钮的文本
            leftText: 'ยกเลิก', //左边按钮的文本
            top: '34%', //弹出框距离页面顶部的距离
            zindex: 9999, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1,修改后黑色遮盖层的z-index是这个数值的-1
            success: function() { //右边按钮的回调函数
                ok_del();
            },
            cancel: function() { //左边按钮的回调函数

            }
        });
    }
});

function ok_del() {
    var line = '.';

    // 记录删除的小说
    var fiction_html = '';
    var fiction_length = $('.fiction_del').length;
    for (var i = 0; i < fiction_length; i++) {
        var del_fiction = $('.fiction_del').eq(i).data("id");
        fiction_html += del_fiction + line;
    }
    fiction_html = fiction_html.substr(0, fiction_html.length - 1);


    // 记录删除的漫画
    var cartoon_html = '';
    var cartoon_length = $('.cartoon_del').length;
    for (var i = 0; i < cartoon_length; i++) {
        var del_cartoon = $('.cartoon_del').eq(i).data("id");
        cartoon_html += del_cartoon + line;
    }
    cartoon_html = cartoon_html.substr(0, cartoon_html.length - 1);


    var data = get_ajax_header();
    $.ajax({
        type: "post",
        url: api_url + "groom/delrack",
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
            "book_id": fiction_html,
            "cartoon_id": cartoon_html
        },
        success: function(res) {
            console.log('删除书本 groom/delrack');
            console.log(res);
            if (res.code == "1") {
                deling = !deling;
                // 还原按钮
                $('.del_button').removeClass("bookRack_delButton_on");
                // $('.del_button').html("删除(0)");
                $('.del_button').html("ลบ(0)");
                // 删除书本
                $('.bookLists_del').remove();

                // 上方导航
                $('.nav_button').show();
                // 编辑完成按钮
                $('.bianji_close').hide();
                // 推荐列表
                $('.recommend_row').show();
                // 设置按钮
                $('.the_book_read ').show();
                $('.the_book_del').hide();

                $('.add_youlike').show();
                $('.youlike').show();
                $('.youlike_title').show();
                $('.bottom_del_nav').hide();


                showToast({
                    text: 'ลบสำเร็จ', //【必填】，否则不能正常显示 , 剩余的其他不是必填
                    bottom: '45%', //toast距离页面底部的距离
                    zindex: 999, //为了防止被其他控件遮盖，z-index默认为2
                    speed: 500, //toast的显示速度
                    time: 3000, //toast显示多久以后消失
                    img: 'yes'
                })

            } else {
                console.log('删除书本 groom/delrack错误');
                check_code(res.code);
            }



        },
        error: function(e) {
            console.log('删除书本 groom/delrack请求失败');
        }
    });




}