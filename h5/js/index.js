check_body_size();
// 设置加载几个AJAX
var load_number = 5;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}

initialization();
// 初始化
function initialization() {
    // 小说banner
    set_fiction_banner();
    // 推荐小说
    set_recommend_fiction();
    // 免费阅读
    setFree_read();
    // 推荐卡通
    setRecommend_cartoon();
    // 热门小说
    setHot_fiction();
}



// 设置小说banner
function set_fiction_banner() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "book/get_banner",
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
            console.log('小说页面banner book/get_banner');
            console.log(res);
            // alert(res);
            if (res.code == "1") {
                var html = '';
                for (var i = 0; i < res.result.length; i++) {
                    html += `<div class="swiper-slide check_goal_type" 
                    data-book_id="` + res.result[i]["book_id"] + `" 
                    data-goal_type="` + res.result[i]["goal_type"] + `"
                    data-goal_window="` + res.result[i]["goal_window"] + `"
                    data-isfree="` + res.result[i]["isfree"] + `"
                    data-scan_seat="` + res.result[i]["scan_seat"] + `"
                    > 
                        <img src="` + res.result[i]["banner_pic"] + `" class="cursor" onerror="this.src='images/replace_2.png'">
                    </div>`;
                }
                // 清除
                $('.fiction_page_banner .swiper-slide').remove();
                // 渲染
                $('.fiction_page_banner .swiper-wrapper').append(html);
                // 驱动小说页面banner轮播
                var fiction_page_banner_Swiper = new Swiper('.fiction_page_banner', {
                    loop: true,
                    pagination: {
                        el: '.fiction_page_banner .swiper-pagination',
                    }
                })


            } else {
                console.log('小说页面banner book/get_banner 错误');
                // alert('小说页面banner book/get_banner 错误');
            }


            check_load();
        },
        error: function(e) {
            check_load();
            // alert('小说页面banner book/get_banner 请求失败');
            console.log('小说页面banner book/get_banner 请求失败');
        }
    });
}


// 初始化 推荐小说
function set_recommend_fiction() {
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
            console.log('推荐小说 book/hotw_groom');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                var length;
                if (res.result.length <= 0) {
                    length = 0;
                    $('.recommend_fiction').hide();
                    $('.recommend_fiction_title').hide();
                } else {
                    length = res.result.length;
                }

                for (var i = 0; i < length; i++) {
                    html += `<div class="swiper-slide go_fiction"
                        data-book_id="` + res.result[i]["book_id"] + `"
                        data-is_virtual="` + res.result[i]["is_virtual"] + `"
                        data-isfree="` + res.result[i]["isfree"] + `"
                    > 
                    <img src="` + res.result[i]["bpic"] + `" class="cursor" onerror="this.src='images/replace_2.png'">
                    <p class="p2-hidden marging_top_0-3 cursor">` + res.result[i]["other_name"] + `</p>
                </div>`;
                }
                // 清除
                $('.recommend_fiction  .swiper-slide').remove();
                // 渲染
                $('.recommend_fiction  .swiper-wrapper').append(html);
                // 驱动推荐小说Swiper
                var recommend_fiction_Swiper = new Swiper('.recommend_fiction', {
                    slidesPerView: 3,
                    spaceBetween: 30,
                    pagination: {
                        el: '.recommend_fiction .swiper-pagination',
                        clickable: true,
                    },
                });


            } else {
                console.log('推荐小说 book/hotw_groom 错误');
            }


            check_load();
        },
        error: function(e) {
            check_load();

            console.log('推荐小说 book/hotw_groom 请求失败');
        }
    });
}

// 初始化 免费阅读
function setFree_read() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "book/get_freeList",
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
            console.log('免费阅读 book/get_freeList');
            console.log(res);

            if (res.code == "1") {
                var length;
                if (res.result.data.length >= 1 && res.result.data.length < 3) {
                    length = res.result.data.length;
                } else if (res.result.data.length >= 3) {
                    length = 3;
                } else if (res.result.data.length <= 0) {
                    length = 0;
                    $('.free_read_title').hide();
                    $('.free_read').hide();
                }

                var html = '';
                for (var i = 0; i < length; i++) {

                    html += `<div class="book_list_css_1 go_fiction" 
                                data-book_id="` + res.result.data[i]["book_id"] + `"
                                data-is_virtual="` + res.result.data[i]["is_virtual"] + `"
                                data-isfree="` + res.result.data[i]["isfree"] + `"
                                data-reward_icon="` + res.result.data[i]["reward_icon"] + `"
                                data-update_status="` + res.result.data[i]["update_status"] + `"
                                data-virtual_coin="` + res.result.data[i]["virtual_coin"] + `"
                                >
                                <img src="` + res.result.data[i]["bpic"] + `" class="cursor" onerror="this.src='images/replace_2.png'">
                                <p class="p2-hidden marging_top_0-3 cursor">` + res.result.data[i]["other_name"] + `</p>
                            </div>`;
                }
                // 清除
                $('.free_read .book_list_css_1').remove();
                // 渲染
                $('.free_read').append(html);


            } else {
                console.log('免费阅读 book/get_freeList 错误');
            }


            check_load();
        },
        error: function(e) {
            check_load();

            console.log('免费阅读 book/get_freeList 请求失败');
        }
    });
}

// 初始化推荐卡通
function setRecommend_cartoon() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "cartoon/get_cartoonList",
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
            console.log('推荐卡通  cartoon/get_cartoonList');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                var length;
                if (res.result.length >= 1 && res.result.length < 3) {
                    length = res.result.length;
                } else if (res.result.length >= 3) {
                    length = 3;
                } else if (res.result.length <= 0) {
                    length = 0;
                    $('.recommend_cartoon').hide();
                    $('.recommend_cartoon_title').hide();
                }
                for (var i = 0; i < length; i++) {

                    html += `<div class="book_list_css_1 go_cartoon" 
                                data-cartoon_id="` + res.result[i]["cartoon_id"] + `"
                                data-is_virtual="` + res.result[i]["is_virtual"] + `"
                                data-isfree="` + res.result[i]["isfree"] + `"
                                data-reward_icon="` + res.result[i]["reward_icon"] + `"
                                data-update_status="` + res.result[i]["update_status"] + `"
                                data-virtual_coin="` + res.result[i]["virtual_coin"] + `"
                                >
                                <img src="` + res.result[i]["bpic"] + `" class="cursor" onerror="this.src='images/replace_2.png'">
                                <p class="p2-hidden marging_top_0-3 cursor">` + res.result[i]["other_name"] + `</p>
                            </div>`;
                }
                // 清除
                $('.recommend_cartoon  .book_list_css_1').remove();
                // 渲染
                $('.recommend_cartoon').append(html);



            } else {
                console.log('推荐卡通  cartoon/get_cartoonList 错误');
            }

            check_load();
        },
        error: function(e) {
            check_load();

            console.log('推荐卡通  cartoon/get_cartoonList 请求失败');
        }
    });
}


// 初始化热门小说
function setHot_fiction() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "book/get_bookList",
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
            console.log('热门小说  book/get_bookList');
            console.log(res);

            if (res.code == "1") {
                var length;
                if (res.result.length >= 1 && res.result.length < 3) {
                    length = res.result.length;
                } else if (res.result.length >= 3) {
                    length = 3;
                } else if (res.result.length <= 0) {
                    length = 0;
                    $('.hot_fiction').hide();
                    $('.hot_fiction_title').hide();
                }
                var html = '';

                for (var i = 0; i < length; i++) {
                    var text = "";
                    if (res.result[i]["update_status"] == "2") {
                        text = 'จบแล้ว';
                    } else {
                        text = 'กำลังเชื่อมต่อ';
                    }
                    // res.result.data[i]
                    html += `<div class="book_list_css_2 go_fiction" 
                            data-book_id="` + res.result[i]["book_id"] + `">
                                <img src="` + res.result[i]["bpic"] + `" class="cursor cover" onerror="this.src='images/replace_2.png'">
                                <div class="row">
                                    <p class="p1-hidden cursor">` + res.result[i]["writer_name"] + `</p>
                                    <p class="p1-hidden cursor">` + res.result[i]["other_name"] + `</p>
                                    <p class="p2-hidden cursor">` + res.result[i]["desc"] + `</p>
                                    <div class="state">
                                        <div>
                                            <i>` + text + `</i>
                                        </div>
                                        <div>
                                            <span class="cursor">อ่านทันที</span>
                                            <img src="images/go_right@2x.png">
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                }
                // // 清除
                $('.hot_fiction .book_list_css_2').remove();
                // // 渲染
                $('.hot_fiction').append(html);



            } else {
                console.log('热门小说  book/get_bookList 错误');
            }


            check_load();
        },
        error: function(e) {
            check_load();

            console.log('热门小说  book/get_bookList 请求失败');
        }
    });
}