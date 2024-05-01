content_size();

function content_size() {
    var body_height = window.innerHeight;
    if (body_height >= 800) {
        $('.bottom_nav').css({
            "height": '1.2rem'
        });

    } else {
        $('.bottom_nav').css({
            "height": '0.8rem'
        });
    }
    window.addEventListener('resize', function() {
        $('html').css('font-size', document.body.clientWidth / 7.5 + 'px');
        // console.log(window.innerHeight);
        var body_height = window.innerHeight;
        if (body_height >= 800) {
            $('.bottom_nav').css({
                "height": '1.2rem'
            });

        } else {
            $('.bottom_nav').css({
                "height": '0.8rem'
            });
        }
    })
}

// 设置加载几个AJAX
var load_number = 2;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        setTimeout(function() {
            load_end();
        }, 1000);
    }
}



// 获取token 
var token = localStorage.getItem('token');
var user_id = localStorage.getItem('user_id');

var book_id = GetQueryString("book_id");

var book_content_type = localStorage.getItem('book_content_type');



var list;
// 记录下标
var index_id;
// 章节参数
var section_id = GetQueryString("section_id");

var this_url = window.location.href;
console.log(this_url);
var night_mode_icon = localStorage.getItem('night_mode_icon');

var bg_color;
// 需要付费的金额
var coin;


check_section_id();
// 检查是否带章节参数
function check_section_id() {
    if (section_id) {
        // console.log("章节参数：" + section_id);
    } else if (!section_id) {
        // console.log("没有章节参数");
        // 历史阅读章数
        var keeping_track = localStorage.getItem('book_id' + book_id);
        // console.log("获取历史阅读章节：" + keeping_track);
        if (keeping_track) {
            section_id = keeping_track;
        }
    }

    // 初始化
    initialization();
}


// 初始化
function initialization() {
    // 判断是否夜晚模式
    night_mode_icon = localStorage.getItem('night_mode_icon');
    bg_color = localStorage.getItem('bg_color');
    if (night_mode_icon) {
        $('body').removeClass(bg_color);
        $('body').addClass('night');
        // 月亮模式图片
        $('#night_mode_icon img').attr('src', 'images/yd_moshi@2x.png');

    } else if (bg_color) {
        $('body').removeClass('night');

        $('body').addClass(bg_color);

        $('.bg_color_page .' + bg_color).addClass('onBg');

        $('#night_mode_icon img').attr('src', 'images/yd_huyan@2x.png');

    } else {
        $('body').removeClass('night');

        $('body').addClass(bg_color);

        $('.bg_color_page .' + bg_color).addClass('onBg');
        // 太阳模式图片
        $('#night_mode_icon img').attr('src', 'images/yd_huyan@2x.png');
    }





    // 检查是否有登录
    if (token) {
        console.log('已经登陆');
        get_remainder();
    } else {
        console.log("未登录");
    }
    get_section();
    get_book_detail();

}


// 获取详情
function get_book_detail() {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + "book/get_bookDetail",
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
            "book_id": book_id
        },
        success: function(res) {
            console.log('获取详情 book/get_bookDetail');
            console.log(res);

            if (res.code == "1") {
                //设置加入书架按钮
                var isCollect = res.result.data.isCollect;
                if (isCollect == 0) {


                } else if (isCollect == 1) {
                    $('.add_bookrack_button').hide();
                }

                // 书名字
                $('.book_detail_book_name').html(res.result.data.other_name);
                // 书封面图片
                $('.book_detail img').attr("src", res.result.data.bpic);
                // 作者
                $('.book_detail_writer_name').html(res.result.data.writer_name);

            } else {
                console.log('获取详情 book/get_bookDetail错误');
                check_code(res.code);
            }
            check_load();


        },
        error: function(e) {
            console.log('获取详情 book/get_bookDetail请求失败');
            check_load();
        }
    });
}



// 获取列表信息
function get_section() {

    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "book/get_section",
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
            book_id: book_id
        },
        success: function(data) {
            console.log("获取列表信息 book/get_section");
            console.log(data);
            var code = data.code;
            // code40001(code);
            var result = data.result;
            if (code == "1") {
                list = result;
                // 设置第一次阅读
                if (!section_id) {

                    section_id = list[0]["section_id"];
                    console.log("第一次阅读,设置section_id： " + section_id + " 为历史阅读章节")
                }
                // 设置历史阅读章节
                localStorage.setItem('book_id' + book_id, section_id);
                var the_url = window_url + 'book_content.html?book_id=' + book_id + '&section_id=' + section_id;
                history.replaceState('', '', the_url);

                // 判断是否有下标
                if (!index_id) {
                    for (var i = 0; i < list.length; i++) {
                        if (section_id == list[i]["section_id"]) {
                            index_id = i;
                            console.log(list[i]);
                        }
                    }

                }

                var html = '';
                for (var i = 0; i < result.length; i++) {
                    var lock = '';
                    if (result[i]["isfree"] != 0) {
                        if (result[i]["ispay"] == 0) {
                            lock = `<img src="images/suo@2x.png" />`;
                        } else if (result[i]["ispay"] == 1) {
                            // lock = `<img src="images/unlock_icon.png" />`;
                        }
                    }


                    html += `<li class="list_ cursor" data-bid="` + result[i]["book_id"] + `" data-section_id=` + result[i]["section_id"] + ` id="md` + result[i]["section_id"] + `">
                    <p class="p1-hidden">` + result[i]["title"] + `</p>` + lock + `</li>`;
                    // console.log(html);
                }
                $('.list ul').append(html);
                console.log("当前章节下标为：" + index_id + ",当前章节=" + section_id);
                setpage_button();
                // 记录是否付费
                var ispay = list[index_id]["ispay"];
                // 需要付费的金额
                coin = list[index_id]["coin"];
                // 是否免费
                var isfree = list[index_id]["isfree"];
                console.log("是否付费ispay:" + ispay);
                console.log("需要付费的金额coin" + coin);
                console.log("是否免费isfree" + isfree);
                // 设置标题
                $('.main_title').html(list[index_id]["title"]);
                $(document).attr("title", list[index_id]["title"]);
                if (isfree == 0) {
                    console.log("该章节免费");
                    // 免费
                    get_wap_content();

                } else {

                    console.log("该章节需要收费");
                    if (ispay == 1) {
                        console.log("该章节已经解锁");
                        get_wap_content();
                    } else {
                        console.log("该章节未解锁");
                        if (token) {
                            $('.need_coin span').html(coin);
                            var my_icon = localStorage.getItem('bookcoin');
                            if (!my_icon) {
                                my_icon = 0;
                            }
                            $('.my_coin span').html(my_icon);
                            $('.unlock_page').show();

                        } else {
                            $('.need_coin span').html(coin);
                            $('.my_coin span').html('0');
                            $('.unlock_page').show();
                            $('.unlock button').click(function() {
                                go_login();
                            });
                        }
                    }
                    check_load();
                }


            } else {
                console.log("获取列表信息 book/get_section错误");
                check_code(res.code);
            }
            check_load();
        },
        error: function(e) {
            console.log("获取列表信息 book/get_section 请求失败");
            check_load();
        }
    });
}
// 设置章节详情
function get_wap_content() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "book/get_wap_content",
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
            book_id: book_id,
            section_id: section_id
        },
        success: function(data) {
            console.log("设置章节详情 book/get_wap_content");
            console.log(data);
            var code = data.code;
            var result = data.result;
            // code40001(code);
            if (code == "1") {
                var font_size = localStorage.getItem('font_size');
                if (!font_size) {
                    font_size = 20;
                    localStorage.setItem('font_size', font_size);
                    $('.font_size_').html(font_size);

                } else {
                    $('.font_size_').html(font_size);

                }
                $('.main_title').html(result.title);
                $('.main_content').html("<pre style='white-space: pre-wrap;font-size:" + font_size + "px'>" + result.sec_content + "</pre>");
                $('.main_button').show();
                $('.main_title').hide();


            } else {
                console.log("设置章节详情 book/get_wap_content错误");
                check_code(res.code);
            }
            check_load();
        },
        error: function(e) {
            console.log("设置章节详情 book/get_wap_content 请求失败");
            check_load();
        }
    });
}


// 解锁文章
function deblocking(coin, section_id) {
    console.log(coin + ' ' + section_id);
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "user/deblocking",
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
            "book_id": book_id,
            "expend_red": coin,
            "section_id": section_id,
            "isauto": 0


        },
        success: function(res) {
            console.log('解锁文章接口 user/deblocking');
            console.log(res);
            var code = res.code;
            var result = res.result;
            // code40001(code);
            if (code == "1") {
                get_remainder();
                $('.unlock_page').fadeOut();
                // showToast({
                //     text: 'Berhasil Buka Kunci',
                //     bottom: '10%',
                //     zindex: 9999,
                //     speed: 500,
                //     time: 3000
                // });
                get_wap_content();
            } else {
                console.log('解锁文章接口 user/deblocking 错误');
                check_code(res.code);
            }
        },
        error: function(e) {
            console.log('解锁文章接口 user/deblocking 请求失败');
            check_load();
        }
    });
}


$('#header-backButton').on('click', function() {
    goBack();
});


$('.unlock_page_colse').on('click', function() {
    goBack();
});

// $('.main button').on('click', function() {
//     var section_id_next = parseInt(section_id) + 1;
//     console.log(section_id);
//     console.log(section_id_next);
//     // location.href = 'book_content.html?book_id=' + book_id + '&section_id=' + section_id_next;
// });

// 设置翻页按钮
function setpage_button() {
    // 下一页
    var section_next = parseInt(index_id) + 1;
    if (list[section_next]) {
        console.log("还有下一章");
        $('#next_page').addClass('main_button_on');
    } else {
        // $('#next_page').removeClass('main_button_on');
        $('#next_page').addClass('no_next');
    }
    // // 上一页
    var section_previous = parseInt(index_id) - 1;
    if (list[section_previous]) {
        $('#previous_page').addClass('main_button_on');
    }

}
// 下一章
$('#next_page').on('click', function() {
    console.log(1);
    if ($(this).is('.no_next')) {
        location.href = 'no_next.html?book_id=' + book_id;
    } else if ($(this).is('.main_button_on')) {
        index_id = parseInt(index_id) + 1;
        section_id = list[index_id]["section_id"];
        console.log("下一章：" + section_id);

        clear_all();
        check_section_id();
    }
});
// 上一章
$('#previous_page').on('click', function() {
    console.log(1);
    if ($(this).is('.main_button_on')) {
        index_id = parseInt(index_id) - 1;
        section_id = list[index_id]["section_id"];
        console.log("上一章" + section_id);

        clear_all();
        check_section_id();
    }

});
// 清除
function clear_all() {
    load_number = 3;
    $('.load-mask').show();
    // $('#next_page').removeClass('main_button_on');
    $('#previous_page').removeClass('main_button_on');
    $('.main_title').html('');
    $('.main_content pre').html('');
    $('.main_button').hide();
    $('.loading').show();
}

//获取用户余额


function get_remainder() {
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
        data: {},
        success: function(data) {
            console.log("获取用户余额 user/remainder");
            var code = data.code;
            // code40001(code);
            console.log(data);

            if (code == "1") {
                if (data.result) {
                    localStorage.setItem("bookcoin", data.result);
                }
            }





        },
        error: function(e) {
            console.log("获取用户余额 user/remainder ");

        }
    });

}




// -_________________________________________________________________________
// 下方导航栏控制
$('.center_button').on('click', function() {
    console.log(1);
    if ($('.bottom_nav').is('.onHide')) {
        $('.bottom_nav').removeClass('onHide');
        $('.bottom_nav').addClass('onShow');
        $('.header').show();
    } else {
        $('.bottom_nav').removeClass('onShow');
        $('.bottom_nav').addClass('onHide');
        $('.header').hide();
        $('.font_icon_page').hide();
    }
});

// 打开目录
$('#menu_icon').on('click', function() {
    $('.menu_icon_page').fadeIn();
});
//关闭目录
$('.menu_icon_page_close').on('click', function() {
    $('.menu_icon_page').fadeOut();

});

$('.list').on('click', '.list_', function(event) {
    section_id = $(this).data('section_id');
    console.log("下一章：" + section_id);
    index_id = '';
    clear_all();
    check_section_id();
    $('.menu_icon_page').fadeOut();
});


// 夜间阅读模式
$('#night_mode_icon').on('click', function() {
    if (night_mode_icon) {
        $('body').removeClass("night");
        night_mode_icon = '';
        localStorage.setItem('night_mode_icon', '');
        bg_color = localStorage.getItem('bg_color');
        $('body').addClass(bg_color);

        $('.bg_color_page .' + bg_color).addClass('onBg');
        // 月亮模式图片
        $('#night_mode_icon img').attr('src', 'images/yd_huyan@2x.png');
    } else {
        bg_color = localStorage.getItem('bg_color');
        $('body').removeClass(bg_color);
        $('body').addClass('night');
        night_mode_icon = 1;
        localStorage.setItem('night_mode_icon', '1');
        // 月亮模式图片
        $('#night_mode_icon img').attr('src', 'images/yd_moshi@2x.png');

    }
});

// 字体选择
$('#font_icon').on('click', function() {
    if ($('.font_icon_page').is('.onHide')) {
        $('.font_icon_page').removeClass('onHide');
        $('.font_icon_page').show();
    } else {
        $('.font_icon_page').hide();
        $('.font_icon_page').addClass('onHide');
    }
});

// 字体加大
$('.font_add').on('click', function() {

    var font_size = parseInt(localStorage.getItem('font_size'));
    if (font_size < 32) {
        font_size = font_size + 1;
        $('pre').css({
            'font-size': font_size
        });
        localStorage.setItem('font_size', font_size);
        $('.font_size_').html(font_size);
    }

});
// 字体减小
$('.font_cut_down').on('click', function() {

    var font_size = parseInt(localStorage.getItem('font_size'));
    if (font_size > 8) {
        font_size = font_size - 1;
        $('pre').css({
            'font-size': font_size
        });
        localStorage.setItem('font_size', font_size);
        $('.font_size_').html(font_size);
    }

});


$('.bg_color_page div').on('click', function() {
    if ($(this).is('.onBg')) {
        $(this).removeClass('onBg');
        for (var i = 1; i <= 12; i++) {
            $('body').removeClass('bg_color_' + i);
        }
        localStorage.setItem('bg_color', '')
    } else {
        for (var i = 1; i <= 8; i++) {
            $('body').removeClass('bg_color_' + i);
        }
        $('.bg_color_page div').removeClass('onBg');
        $(this).addClass('onBg');
        bg_color = $(this).data('bg_color');
        localStorage.setItem('bg_color', bg_color);
        $('body').addClass(bg_color);
    }
});

$('#review_icon').on('click', function() {

    location.href = 'discuss.html?book_id=' + book_id;
});

// 加入书架
$('.add_bookrack_button').on('click', function() {

    add_rack()
});

// 加入书架
function add_rack() {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + "groom/add_rack",
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
            "book_id": book_id,
            "type": 1
        },
        success: function(res) {

            console.log('加入书架 groom/add_rack');
            console.log(res);

            if (res.code == "1") {
                $('.add_bookrack_button').hide();
                showToast({
                    text: 'เพิ่มเข้าคลังแล้ว', //【必填】，否则不能正常显示 , 剩余的其他不是必填
                    top: '45%', //toast距离页面底部的距离
                    zindex: 50, //为了防止被其他控件遮盖，z-index默认为2
                    speed: 500, //toast的显示速度
                    time: 2000, //toast显示多久以后消失
                    img: 'yes'
                });


            } else {
                check_code(res.code);
            }
        },
        error: function(e) {
            console.log("groom/add_rack 接口错误");
        }
    });
}

$('.unlock button').click(function() {
    deblocking(coin, section_id);
});


$('.error_button ').on('click', function(event) {
    if (check_token) {
        location.href = "error.html?section_id=" + section_id + "&book_id=" + book_id;
    } else {
        go_login();
    }
});