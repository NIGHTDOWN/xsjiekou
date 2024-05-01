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
    // 卡通banner
    set_cartoon_banner();
    // 推荐漫画
    set_recommend_cartoon();
    // 热门卡通
    sethot_cartoon();

}



// 设置卡通banner
function set_cartoon_banner() {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + "cartoon/get_banner",
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
            console.log('卡通banner cartoon/get_banner');
            console.log(res);

            if (res.code == "1") {

                var length;
                if (res.result.length >= 1 && res.result.length < 3) {
                    length = res.result.length;
                } else if (res.result.length >= 3) {
                    length = 3;
                } else if (res.result.length <= 0) {
                    length = 0;
                    $('.cartoon_page_banner').hide();
                }

                var html = '';
                for (var i = 0; i < length; i++) {
                    html += `<div class="swiper-slide check_goal_type" 
                    data-cartoon_id="` + res.result[i]["book_id"] + `" 
                    data-goal_type="` + res.result[i]["goal_type"] + `"
                    data-goal_window="` + res.result[i]["goal_window"] + `"
                    data-isfre  e="` + res.result[i]["isfree"] + `"
                    data-scan_seat="` + res.result[i]["scan_seat"] + `"
                    > 
                        <img src="` + res.result[i]["banner_pic"] + `" class="cursor" onerror="this.src='images/replace_2.png'">
                    </div>`;
                }
                // 清除
                $('.cartoon_page_banner .swiper-slide').remove();
                // 渲染
                $('.cartoon_page_banner .swiper-wrapper').append(html);
                // 驱动小说页面banner轮播
                var cartoon_page_banner_Swiper = new Swiper('.cartoon_page_banner', {
                    loop: true,
                    pagination: {
                        el: '.cartoon_page_banner .swiper-pagination',
                    }
                })


            } else {
                console.log('卡通banner cartoon/get_banner 错误');
            }


            check_load()
        },
        error: function(e) {
            console.log('卡通banner cartoon/get_banner 请求失败');
            check_load();
        }
    });
}


// 初始化 推荐漫画
function set_recommend_cartoon() {
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
            console.log('推荐漫画 cartoon/get_cartoonList');
            console.log(res);

            if (res.code == "1") {
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
                var html = '';
                for (var i = 0; i < length; i++) {
                    html += `<div class="swiper-slide go_cartoon"
                        data-cartoon_id="` + res.result[i]["cartoon_id"] + `"
                        data-is_virtual="` + res.result[i]["is_virtual"] + `"
                        data-isfree="` + res.result[i]["isfree"] + `"
                    > 
                    <img src="` + res.result[i]["bpic"] + `" class="cursor" onerror="this.src='images/replace_2.png'">
                    <p class="p2-hidden marging_top_0-3 cursor">` + res.result[i]["other_name"] + `</p>
                </div>`;
                }
                // 清除
                $('.recommend_cartoon  .swiper-slide').remove();
                // 渲染
                $('.recommend_cartoon  .swiper-wrapper').append(html);
                // 驱动推荐漫画Swiper
                var recommend_cartoon_Swiper = new Swiper('.recommend_cartoon', {
                    slidesPerView: 3,
                    spaceBetween: 30,
                    pagination: {
                        el: '.recommend_cartoon .swiper-pagination',
                        clickable: true,
                    },
                });


            } else {
                console.log('推荐漫画 cartoon/get_cartoonList错误');
            }

            check_load()

        },
        error: function(e) {
            console.log('推荐漫画 cartoon/get_cartoonList 请求失败');
            check_load();
        }
    });
}



// 初始化热门漫画
function sethot_cartoon() {
    var data = get_ajax_header();


    $.ajax({
        type: "post",
        url: api_url + "cartoon/hot_cart_groom",
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
            console.log('热门漫画  cartoon/hot_cart_groom');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                var result = res.result;
                var length_;
                if (result.length >= 3) {
                    length_ = 3;
                } else if (result.length > 0) {
                    length_ = result.length;
                } else if (result.length == 0) {
                    $('.hot_cartoon_page').hide();
                }

                for (var i = 0; i < result.length; i++) {
                    var text = "";
                    if (result[i]["update_status"] == "2") {
                        text = 'จบแล้ว';
                    } else {
                        text = 'กำลังเชื่อมต่อ';
                    }
                    // result.data[i]
                    html += `<div class="book_list_css_2 go_cartoon" 
                            data-cartoon_id="` + result[i]["cartoon_id"] + `">
                                <img src="` + result[i]["bpic"] + `" class="cursor cover" onerror="this.src='images/replace_2.png'">
                                <div class="row">
                                    <p class="p1-hidden cursor">` + result[i]["writer_name"] + `</p>
                                    <p class="p1-hidden cursor">` + result[i]["other_name"] + `</p>
                                    <p class="p2-hidden cursor">` + result[i]["desc"] + `</p>
                                    <div class="state">
                                        <div>
                                            <i>` + text + `</i>
                                        </div>
                                        <div>
                                            <span class="cursor">อ่าน</span>
                                            <img src="images/go_right@2x.png">
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                }
                // // 清除
                $('.hot_cartoon .book_list_css_2').remove();
                // // 渲染
                $('.hot_cartoon').append(html);



            } else {
                console.log('热门漫画  cartoon/hot_cart_groom 错误');
            }


            check_load();
        },
        error: function(e) {
            console.log('热门漫画  cartoon/hot_cart_groom 请求失败');
            check_load();
        }
    });
}