var book_id = GetQueryString("book_id");
var cartoon_id = GetQueryString("cartoon_id");
// console.log(book_id);

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

    if (book_id) {
        get_book_detail();
        cartoon_id = '';
    } else if (cartoon_id) {
        get_cartoon_detail();
        book_id = '';
    } else {
        goBack();
    }


}

// 获取漫画详情
function get_cartoon_detail() {
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
                    $('.comment_list').after('<p style="text-align:center;padding:0.4rem;font-size:0.24rem;color:#999">ไม่มีความคิดเห็นอีกแล้ว</p>');
                } else {
                    $('.comment_list').after('<p style="text-align:center;padding:0.4rem;font-size:0.24rem;color:#999">ยังไม่มีความคิดเห็น</p>');
                }


            } else {

            }
            check_load();


        },
        error: function(e) {
            console.log("获取详情接口错误");
            check_load();
        }
    });
}


// 小说
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
            "book_id": book_id,
            "cartoon_id": cartoon_id
        },
        success: function(res) {
            console.log('获取详情 book/get_bookDetail');
            console.log(res);

            if (res.code == "1") {
                // 评论区域
                var discuss = res.result.discussd.discuss;
                // console.log(discuss);
                if (discuss.length >= 0) {

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
                    // $('.comment_list').after('<p style="text-align:center;padding:0.4rem;font-size:0.24rem;color:#999">ไม่มีความคิดเห็นอีกแล้ว</p>');
                    $('.comment_list').after('<p style="text-align:center;padding:0.4rem;font-size:0.24rem;color:#999">ไม่มีความคิดเห็นอีกแล้ว</p>');

                } else {
                    // $('.comment_list').after('<p style="text-align:center;padding:0.4rem;font-size:0.24rem;color:#999">ยังไม่มีความคิดเห็น</p>');
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
$('.home_button_img').on('click', function() {
    if (!check_token) {
        go_login();
        return false;
    }
    if (book_id) {
        location.href = 'add_discuss.html?book_id=' + book_id;
    } else {

        location.href = 'add_discuss.html?cartoon_id=' + cartoon_id;
    }
});