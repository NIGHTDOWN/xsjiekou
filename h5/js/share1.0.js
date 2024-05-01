check_body_size();
// 设置加载几个AJAX
var load_number = 0;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}

var book_id = GetQueryString("bid");

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

            if (res.code == 1) {
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

            if (res.code == 1) {
                // 设置目录标题
                var section_id = res.result[0]["section_id"];

                get_section_content(section_id);



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


function get_section_content(section_id) {
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
            console.log("设置章节详情 book/get_wap_content");
            console.log(res);
            var code = res.code;
            var result = res.result;
            // code40001(code);
            if (res.code == 1) {

                $('.articleIntroduces_1 h2').html(result.title);
                $('.articleIntroduces_1 p').html("<p>" + result.sec_content + "</p>");


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

            if (res.code == 1) {
                var html = '';
                for (var i = 0; i < 3; i++) {

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