check_body_size();
// 设置加载几个AJAX
var load_number = 0;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        setTimeout(function() {
            load_end();
        }, 800)

    }
}

var book_id = GetQueryString("bid");
var cartoon_id = GetQueryString("cid");
// 类型
var id_type;
// 可以观看数字
var can_number;
// 目前观看数字
var now_number = 0;
// 可观看章节setction数组
var section_arr = [];

var running = true;


initialization();
// 初始化
function initialization() {
    // 设置类型
    check_id_type();
}

// 设置类型
function check_id_type() {
    if (book_id) {
        id_type = 'book';
    } else if (cartoon_id) {
        id_type = 'cartoon';
    }
    // 获取详情
    get_detail();
}







// 检查获类型详情
function get_detail() {
    if (id_type == 'book') {
        // 获取小说详情
        get_book_detail();
    } else if (id_type = 'cartoon') {
        get_cartoonDetail();
    }
}




// 获取卡通详情
function get_cartoonDetail() {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + "cartoon/get_cartoonDetail",
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
            "cartoon_id": cartoon_id
        },
        success: function(res) {
            console.log('获取详情 cartoon/get_cartoonDetail');
            console.log(res);

            if (res.code == "1") {



                // 书名字
                $('.fiction_detail .book_name').html(res.result.data.other_name);
                $('.book_name_title h2').html(res.result.data.other_name);
                // 书简介
                $('.book_desc_hidden').html(res.result.data.desc);
                // 设置网页标题
                $(document).attr("title", res.result.data.other_name);
                // 书封面图片
                $('.fiction_detail_cover').attr("src", res.result.data.share_banner);
                // 传递设置阅读章节数量
                var see_setction = res.result.data.end_share;
                // var see_setction = "0";
                // var share_url = window.location.href;
                // $('meta[property="og:url"]').attr('content', share_url);
                // $('meta[property="og:title"]').attr('content', res.result.data.other_name);
                // $('meta[property="og:description"]').attr('content', res.result.data.desc);
                // $('meta[property="og:image"]').attr('content', res.result.data.share_banner);
                // 获取漫画目录 并封章节section
                get_cart_section(see_setction);





            } else {
                console.log('获取详情 cartoon/get_cartoonDetail 接口错误');
            }
            check_load();


        },
        error: function(e) {
            console.log('获取详情 cartoon/get_cartoonDetail 请求失败');
            check_load();
        }
    });
}

// 获取小说详情
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

            if (res.code == 1) {
                // 书名字
                $('.fiction_detail .book_name').html(res.result.data.other_name);
                $('.book_name_title h2').html(res.result.data.other_name);
                // 书简介
                $('.book_desc_hidden').html(res.result.data.desc);
                // 设置网页标题
                $(document).attr("title", res.result.data.other_name);
                // 书封面图片
                $('.fiction_detail_cover').attr("src", res.result.data.share_banner);
                // 传递设置阅读章节数量
                // var see_setction = "0";
                var see_setction = res.result.data.end_share;
                // var share_url = window.location.href;
                // // _____________________设置meta________________
                // $('meta[property="og:url"]').attr('content', share_url);
                // $('meta[property="og:title"]').attr('content', res.result.data.other_name);
                // $('meta[property="og:description"]').attr('content', res.result.data.desc);
                // $('meta[property="og:image"]').attr('content', res.result.data.share_banner);
                // 获取小说目录 并封章节section
                get_book_section(see_setction);
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


// 获取小说目录
function get_book_section(see_setction) {
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
            console.log('获取小说目录 book/get_section');
            console.log(res);
            if (res.code == 1) {
                if (see_setction == "0") {
                    can_number = 3;
                } else {
                    // 获取设定的章节，设置可观看章节的数量
                    for (var k = 0; k < res.result.length; k++) {
                        if (res.result[k]["section_id"] == see_setction) {
                            can_number = k;
                        }
                    }


                }
                console.log("can_number" + can_number);
                // 封装可观看章节的ID
                for (var i = 0; i <= can_number; i++) {
                    var section_id = res.result[i]["section_id"];
                    section_arr.push(section_id);
                }
                console.log(section_arr);
                // 获取小说章节内容
                get_book_content();
            } else {
                console.log('获取小说目录 book/get_section 错误');
            }


            check_load();
        },
        error: function(e) {
            console.log('获取小说目录 book/get_section 请求失败');
            check_load();
        }
    });
}


// 获取漫画目录
function get_cart_section(see_setction) {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + "cartoon/get_cart_section",
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
            "cartoon_id": cartoon_id
        },
        success: function(res) {
            console.log('获取目录 cartoon/get_cart_section');
            console.log(res);
            if (res.code == "1") {
                if (see_setction == "0") {
                    can_number = 3;
                } else {
                    // 获取设定的章节，设置可观看章节的数量
                    for (var k = 0; k < res.result.list.length; k++) {
                        // console.log(res.result.list[k]["cart_section_id"]);
                        if (res.result.list[k]["cart_section_id"] == see_setction) {
                            can_number = k;
                            console.log(can_number);
                        }
                    }
                }
                console.log("can_number" + can_number);

                // 封装可观看章节的ID
                for (var i = 0; i <= can_number; i++) {
                    var section_id = res.result.list[i]["cart_section_id"];
                    section_arr.push(section_id);
                }
                console.log(section_arr);
                // 获取漫画章节内容
                get_section_content();
            } else {
                console.log('获取目录 cartoon/get_cart_section 接口错误');
            }
            check_load();
        },
        error: function(e) {
            console.log('获取目录 cartoon/get_cart_section 请求失败');
            check_load();
        }
    });
}

// 获取小说章节内容
function get_book_content() {


    // 获取章节ID
    var section_id = section_arr[now_number];
    console.log("section_id=" + section_id)
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
        success: function(res) {
            console.log("获取小说内容 book/get_wap_content");
            console.log(res);
            var code = res.code;
            var result = res.result;
            // code40001(code);
            if (res.code == 1) {


                var content = `<pre>` + result.sec_content + `</pre>`;
                $('.content').append(content);

                now_number++;


            } else {
                console.log("获取小说内容 book/get_wap_content错误");
                check_code(res.code);
            }
            setTimeout(function() {
                running = true;
            }, 1000)
            check_load();
        },
        error: function(e) {
            console.log("获取小说内容 book/get_wap_content 请求失败");
            check_load();
        }
    });

}


// 设置漫画章节详情
function get_section_content() {
    // 获取章节ID
    var section_id = section_arr[now_number];
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "cartoon/get_wap_content",
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
            "cartoon_id": cartoon_id,
            "cart_section_id": section_id
        },
        success: function(data) {
            console.log("设置章节详情 cartoon/get_cart_sec_content");
            console.log(data);
            var code = data.code;
            var result = data.result;
            var cart_sec_content = data.result.cart_sec_contents.cart_sec_content;
            // code40001(code);
            if (code == "1") {
                $('.content').css({ "padding": "0.6rem 0 0 0" });
                var html = '';
                for (var i = 0; i < cart_sec_content.length; i++) {
                    html += `<img src="` + cart_sec_content[i]["url"] + `" class="cursor" onerror="this.src='images/replace_2.png'"></div>`;
                }
                $('.content').append(html);
                now_number++;

            }
            setTimeout(function() {
                running = true;
            }, 1000)
            check_load();
        },
        error: function(e) {
            console.log("设置章节详情 cartoon/get_cart_sec_content 失败");
            check_load();
        }
    });
}


$(function() {
    //滑动到底部
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height()) {
            console.log("running=" + running)
            if (running) {
                running = false;
                // 是否最后一章
                if (now_number <= can_number) {
                    // 判断类型
                    if (id_type == 'book') {
                        console.log("now_number=" + now_number);
                        get_book_content();

                    } else if (id_type == "cartoon") {

                        console.log("now_number=" + now_number);
                        get_section_content();

                    }
                } else {
                    $('.bottom_download').removeClass('show').addClass("hide");
                    setTimeout(function() {
                        $('.mask').removeClass('hideMask').addClass("showMask");
                    }, 250);
                    setTimeout(function() {
                        running = true;
                    }, 1000);
                    $('body').addClass('noscroll');
                    $(".mask").on("touchmove", function(event) {
                        event.preventDefault();
                    });


                    // alert("最后一章");

                }
            }
        }
    });


    //关闭浮层
    $('.maskClose').click(function() {
        $('body').removeClass('noscroll');
        $('.mask').removeClass('showMask').addClass('hideMask');
        var timer = setTimeout(function() {
            $('.bottom_download').removeClass('hide').addClass('show');
        }, 250);
    });

})