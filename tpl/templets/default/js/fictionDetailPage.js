check_body_size();
// 设置加载几个AJAX
var load_number = 3;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}

var book_id = GetQueryString("book_id");

initialization();
// 初始化
function initialization() {

    // 获取详情
    get_book_detail();
    // 获取目录
    get_book_section();
    // 猜你喜欢
    get_youlike();


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
                    $(this).data("isCollect", 0);
                    $(this).attr("data-isCollect", 0)
                } else if (isCollect == 1) {
                    $(this).data("isCollect", 1);
                    $(this).attr("data-isCollect", 1)
                    // $('.book_rack_button').html("移出书架");
                    $('.book_rack_button').html("เพิ่มเข้าคลังแล้ว");

                }
                $('.book_rack_button').data("isCollect", isCollect)

                // 书名字
                $('.fiction_detail .book_name').html(res.result.data.other_name);
                $(document).attr("title", res.result.data.other_name);
                // 书封面图片
                $('.fiction_detail_cover').attr("src", res.result.data.bpic);
                // 作者
                $('.writer_name span').html(res.result.data.writer_name);
                // 状态
                var status = res.result.data.status;
                if (status == "2") {
                    // $('.book_state span').html("连载中");กำลังเชื่อมต่อ
                    $('.book_state span').html("กำลังเชื่อมต่อ");
                } else if (status == "1") {
                    // $('.book_state span').html("已完结");
                    $('.book_state span').html("จบแล้ว");
                }
                // 描述
                $('.articleIntroduces p').html(res.result.data.desc);
                // 字数
                $('.number_words span').html(res.result.data.wordnum)


                // 人气
                $('.hits_number').html(res.result.data.hits);
                // 评分
                $('.replynum_number').html(res.result.data.replynum);
                // 在读
                $('.read_number').html(res.result.data.collect);
                // ___________________________________________
                // 判断有没有登录





                // 评论区域
                var discuss = res.result.discussd.discuss;
                // console.log(discuss.length);
                if (discuss.length > 0) {
                    var discuss_length = discuss.length;
                    if (discuss_length >= 3) {
                        discuss_length = 3;
                    }
                    var html = '';
                    for (var i = 0; i < discuss_length; i++) {
                        var star = parseFloat(discuss[i]["star"]);
                        var star_img = '';
                        if (star < 2) {
                            star_img = `<img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">`;
                        } else if (star >= 2 && star < 3) {
                            star_img = `<img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">`;
                        } else if (star >= 3 && star < 4) {
                            star_img = `<img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">`;
                        } else if (star >= 4 && star < 5) {
                            star_img = `<img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_Star@2x.png" alt="">`;
                        } else if (star >= 5) {
                            star_img = `<img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">
                                    <img src="images/dp_StarclickCopy2@2x.png" alt="">`;
                        }


                        html += `<div class="comment_lists">
                                <div class="flex-wrap-nowrap flexCenter-ai-center comment_lists_top">
                                    <img src="` + discuss[i]["avater"] + `" alt="" class="comment_lists_head" onerror="this.src='images/replace_2.png'">
                                    <p class="comment_lists_name">` + discuss[i]["nick_name"] + `</p>
                                    <div class="comment_lists_start flex-wrap-nowrap">
                                        ` + star_img + `
                                    </div>
                                </div>
                                <p class="comment_lists_desc">` + discuss[i]["content"] + `</p>
                            </div>`;
                    }
                    $('.comment_list').append(html);
                    $('.comment_list_button span').html(discuss.length);

                } else {
                    $('.comment_list_button').hide();
                    $('.comment_list').after('<p style="text-align:center;padding:0.4rem;font-size:0.24rem;color:#999">ยังไม่มีความคิดเห็น</p>');
                }


            } else {
                console.log('获取详情 book/get_bookDetail 错误');
            }
            check_load();


        },
        error: function(e) {
            console.log('获取详情 book/get_bookDetail 请求失败');
            check_load();
        }
    });
}


// 获取目录
function get_book_section() {
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
            "book_id": book_id
        },
        success: function(res) {
            console.log('获取目录 book/get_section');
            console.log(res);

            if (res.code == "1") {
                // 设置目录标题
                var the_length = res.result.length - 1;
                $('.catalogue_text').html(res.result[the_length]["title"]);
                var html = '';
                for (var i = 0; i < res.result.length; i++) {
                    var isfree = res.result[i]["isfree"];
                    var img_ = '';
                    if (isfree == "0") {

                    } else {
                        if (res.result[i]["ispay"] == 0) {
                            img_ = '<img src="images/suo@2x.png" class="cursor">';
                        }
                    }

                    html += `<li class="list_" 
                    data-book_id="` + res.result[i]["book_id"] + `"
                    data-section_id="` + res.result[i]["section_id"] + `">
                    <p class="p1-hidden cursor">` + res.result[i]["title"] + `</p>
                    ` + img_ + `
                </li>`;
                }
                $('.menu_icon_page .list ul').append(html);





            } else {
                console.log('获取目录 book/get_section 错误');
            }


            check_load();
        },
        error: function(e) {
            console.log('获取目录 book/get_section 请求失败');
            check_load();
        }
    });
}



// 猜你喜欢
function get_youlike() {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + " book/hot_groom",
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

            console.log('猜你喜欢 book/get_section');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                var length;
                if (res.result.length >= 1 && res.result.length < 6) {
                    length = res.result.length;
                } else if (res.result.length >= 6) {
                    length = 6;
                } else if (res.result.length <= 0) {
                    length = 0;
                    $('.youlike').hide();
                    $('.youlike_title').hide();
                }
                for (var i = 0; i < length; i++) {

                    html += `<div class="swiper-slide go_fiction" data-book_id="` + res.result[i]["book_id"] + `"> 
                                <img src="` + res.result[i]["bpic"] + `" class="cursor" onerror="this.src='images/replace_2.png'">
                                <p class="p2-hidden marging_top_0-3">` + res.result[i]["other_name"] + `</p>
                            </div>`;
                }
                $('.youlike .swiper-wrapper').append(html);

                // 驱动猜你喜欢Swiper
                var youlike_Swiper = new Swiper('.youlike', {
                    slidesPerView: 3,
                    spaceBetween: 30,
                    pagination: {
                        el: '.youlike .swiper-pagination',
                        clickable: true,
                    },
                });



            } else {
                console.log('猜你喜欢 book/get_section 错误');
            }


            check_load();
        },
        error: function(e) {
            console.log('猜你喜欢 book/get_section 请求失败');
            check_load();
        }
    });
}



// 关闭目录页面
$('.menu_icon_page_close').on("click", function() {
    $('.menu_icon_page').fadeOut();
});
$('.catalogue_button').on("click", function() {
    $('.menu_icon_page').fadeIn();
})

// 点击阅读
$('.book_rack_button').on('click', function() {
    // alert(1);
    if (check_token) {
        var isCollect = $(this).data("isCollect");
        console.log(isCollect);
        if (isCollect == 0) {
            add_rack();

        } else if (isCollect == 1) {


            showConfirm({
                // 确认移除书架？
                text: 'ยินยันจะย้ายออกคลัง', //【必填】，否则不能正常显示
                // 确认
                rightText: 'ยืนยัน', //右边按钮的文本
                rightBgColor: '#ff5026', //右边按钮的背景颜色，【不能设置为白色背景】
                rightColor: '#fff', //右边按钮的文本颜色，默认白色
                // 取消
                leftText: 'ยกเลิก', //左边按钮的文本
                top: '34%', //弹出框距离页面顶部的距离
                zindex: 50, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1,修改后黑色遮盖层的z-index是这个数值的-1
                success: function() { //右边按钮的回调函数
                    remove_rack();
                },
                cancel: function() { //左边按钮的回调函数

                }
            });
        }
    } else {
        go_login();
    }

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
                $('.book_rack_button').html("เพิ่มเข้าคลังแล้ว");
                $('.book_rack_button').data("isCollect", 1);
                $('.book_rack_button').attr("data-isCollect", 1);
                showToast({
                    text: 'เพิ่มเข้าคลังแล้ว', //【必填】，否则不能正常显示 , 剩余的其他不是必填
                    top: '45%', //toast距离页面底部的距离
                    zindex: 50, //为了防止被其他控件遮盖，z-index默认为2
                    speed: 500, //toast的显示速度
                    time: 2000, //toast显示多久以后消失
                    img: 'yes'
                });

            } else {
                console.log('加入书架 groom/add_rack错误');
                check_code(res.code);
            }
        },
        error: function(e) {
            console.log('加入书架 groom/add_rack请求失败');
        }
    });
}



// 移出书架
function remove_rack() {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + "  groom/delrack",
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

            console.log('移出书架 groom/delrack');
            console.log(res);

            if (res.code == "1") {

                // $('.book_rack_button').html("加入书架");
                $('.book_rack_button').html("เพิ่มเข้าคลัง");
                $('.book_rack_button').data("isCollect", 0);
                $('.book_rack_button').attr("data-isCollect", 0);
                showToast({
                    // 成功移除书架
                    text: 'ย้ายออกคลังสำเร็จ', //【必填】，否则不能正常显示 , 剩余的其他不是必填
                    bottom: '10%', //toast距离页面底部的距离
                    zindex: 50, //为了防止被其他控件遮盖，z-index默认为2
                    speed: 500, //toast的显示速度
                    time: 2000, //toast显示多久以后消失
                    img: 'yes'
                });

            } else {
                console.log('移出书架 groom/delrack 错误');
                check_code(res.code);
            }
        },
        error: function(e) {
            console.log('移出书架 groom/delrack 请求失败');
        }
    });
}

// 点击阅读
$('.book_read_button').on('click', function(event) {
    var section_id = localStorage.getItem("book_id" + book_id);
    if (section_id) {
        location.href = 'book_content.html?book_id=' + book_id + '&section_id=' + section_id;
    } else {
        section_id = $('.menu_icon_page .list_').eq(0).data("section_id");
        location.href = 'book_content.html?book_id=' + book_id + '&section_id=' + section_id;
    }
});
// 点击写评论
$('.comment_title_button').on('click', function() {
    var check_token = localStorage.getItem("token");
    if (check_token) {
        location.href = "add_discuss.html?book_id=" + book_id;
    } else {
        go_login();
    }
});

// 选择章节
$(".menu_icon_page .list_").on('click', function() {
    location.href = ''
});
$('body').on('click', '.menu_icon_page .list_', function(event) {
    var book_id = $(this).data("book_id");
    var section_id = $(this).data("section_id");
    location.href = 'book_content.html?book_id=' + book_id + '&section_id=' + section_id;
});


// 查看更多评论
$('.comment_list_button').on('click', function() {
    location.href = 'discuss.html?book_id=' + book_id;
});