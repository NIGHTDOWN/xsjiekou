check_body_size();


var running = true;
// 设置加载几个AJAX
var load_number = 1;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}


var book_id = GetQueryString("book_id");
var cartoon_id = GetQueryString("cartoon_id");
var id_type;
var id_type_number;

check_id_type();

function check_id_type() {
    get_youlike();
    if (book_id) {
        id_type_number = book_id;
        id_type = 1;
        get_book_detail();
    } else if (cartoon_id) {
        id_type_number = cartoon_id;
        id_type = 2;
        get_cartoonDetail();
    } else {

    }
}


// 获取book详情
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
                if (isCollect == 0) {} else if (isCollect == 1) {
                    $('.home_button').hide();
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

// 获取cartoon详情
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
                //设置加入书架按钮
                var isCollect = res.result.data.isCollect;
                if (isCollect == 0) {} else if (isCollect == 1) {
                    $('.home_button').hide();
                }
            } else {
                console.log('获取详情 cartoon/get_cartoonDetail 失败');
            }
            check_load();
        },
        error: function(e) {
            console.log('获取详情 cartoon/get_cartoonDetail 请求失败');
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
        data: {},
        success: function(res) {

            console.log('猜你喜欢 book/get_section');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                var length = res.result.length;
                if (res.result.length > 6) {
                    length = 6;
                } else {
                    length = res.result.length;
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
                check_code(res.code)
            }


            check_load();
        },
        error: function(e) {
            console.log('猜你喜欢 book/get_section 失败');
            check_load();
        }
    });
}


// 点击催更
$('.cuigen_button_page button').on('click', function() {
    if ($(this).hasClass('isClick')) {

    } else {
        urge();
    }
});

// 催更
function urge() {
    var data = get_ajax_header();
    $.ajax({
        type: "post",
        url: api_url + "user/urge",
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
            "type": id_type,
            "wid": id_type_number
        },
        success: function(res) {

            console.log('催更 user/urge');
            console.log(res);

            if (res.code == "1") {

                $('.cuigen_button_page button').css({
                    "background": '#999'
                });
                $('.cuigen_button_page button').addClass("isClick");
                $('.cuigen_button_page button').html("เร่งอัพเดทแล้ว");
                showToast({
                    text: 'เร่งอัพเดทสำเร็จ',
                    top: '45%',
                    zindex: 9999,
                    speed: 500,
                    time: 3000,
                    img: 'yes'
                });

            } else {
                console.log('催更 user/urge 错误');
                check_code(res.code)
            }


            check_load();
        },
        error: function(e) {
            console.log('催更 user/urge 请求失败');
        }
    });
}



// 加入书架
$('.add_bookrack_button').on('click', function() {
    if (running) {
        running = !running;
        add_rack();
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
            "book_id": id_type_number,
            "type": id_type
        },
        success: function(res) {
            running = !running;

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
                console.log('加入书架 groom/add_rack 错误');
                check_code(res.code);
            }
            running = !running;
        },
        error: function(e) {
            running = !running;
            console.log('加入书架 groom/add_rack 请求失败');
        }
    });
}