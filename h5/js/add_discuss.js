var book_id = GetQueryString("book_id");
var cartoon_id = GetQueryString("cartoon_id");
check_body_size();

// 设置加载几个AJAX
var load_number = 1;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        load_end();
    }
}


// 设置评分 星级
var star = 5;
$('.img_page').on('click', 'img', function(event) {
    var number = $(this).data('number');
    if (number != star) {
        star = number;
        console.log(number);


        var html = '';
        switch (number) {
            case 1:
                html = `<img src="images/dp_StarclickCopy2@2x.png" data-number="1">
                <img src="images/dp_Star@2x.png" data-number="2">
                <img src="images/dp_Star@2x.png" data-number="3">
                <img src="images/dp_Star@2x.png" data-number="4">
                <img src="images/dp_Star@2x.png" data-number="5">`;
                break;
                // default:
            case 2:
                html = `<img src="images/dp_StarclickCopy2@2x.png" data-number="1">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="2">
                <img src="images/dp_Star@2x.png" data-number="3">
                <img src="images/dp_Star@2x.png" data-number="4">
                <img src="images/dp_Star@2x.png" data-number="5">`;
                break;
            case 3:
                html = `<img src="images/dp_StarclickCopy2@2x.png" data-number="1">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="2">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="3">
                <img src="images/dp_Star@2x.png" data-number="4">
                <img src="images/dp_Star@2x.png" data-number="5">`;
                break;
            case 4:
                html = `<img src="images/dp_StarclickCopy2@2x.png" data-number="1">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="2">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="3">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="4">
                <img src="images/dp_Star@2x.png" data-number="5">`;
                break;
            case 5:
                html = `<img src="images/dp_StarclickCopy2@2x.png" data-number="1">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="2">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="3">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="4">
                <img src="images/dp_StarclickCopy2@2x.png" data-number="5">`;
                break;
        }
        $('.img_page img').remove();
        $('.img_page').append(html);
    }
});



check_type();
// 检查是小说还是漫画
function check_type() {
    if (book_id) {
        console.log("获取小说详情");
        get_book();
    } else if (cartoon_id) {
        console.log("获取漫画详情");
        get_cartoon();
    }
}


// 获取小说详情
function get_book() {
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
            console.log('获取小说详情 book/get_bookDetail');
            console.log(res);
            if (res.code == "1") {
                // 书本封面
                $('.book_show_cover').attr("src", res.result.data.bpic);
                // 书本名字
                $('.book_show_name').html(res.result.data.other_name);
                // 作者
                $('.book_show_writer_name span').html(res.result.data.writer_name);

            } else {
                console.log('获取小说详情book/get_bookDetail错误 ');
                check_code(res.code);
            }

            check_load();

        },
        error: function(e) {
            check_load();
            console.log('获取小说详情 请求失败');
        }
    });
}

// 获取漫画详情
function get_cartoon() {
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
            console.log('获取漫画详情 cartoon/get_cartoonDetail');
            console.log(res);
            if (res.code == 1) {
                // 书本封面
                $('.book_show_cover').attr("src", res.result.data.bpic);
                // 书本名字
                $('.book_show_name').html(res.result.data.other_name);
                // 作者
                $('.book_show_writer_name span').html(res.result.data.writer_name);

            } else {
                console.log('获取漫画详情cartoon/get_cartoonDetail 错误');
                check_code(res.code);
            }



            check_load();
        },
        error: function(e) {
            console.log('获取漫画详情 请求失败');
            check_load();
        }
    });
}



// 提交评论
$('.discuss_button').on('click', function() {
    var text = $('.discuss_textarea').val();
    if (text) {

        var check_time = check_discuss_time();
        console.log(check_time);
        if (check_time) {
            if (book_id) {
                add_discuss_book();
            } else if (cartoon_id) {
                add_discuss_cartoon();
            }
        }

    } else {
        check_code(res.code);
    }

});



// 添加小说评论
function add_discuss_book() {
    var content = $(".discuss_textarea").val();


    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "user/discuss",
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
            "star": star,
            "content": content
        },
        success: function(res) {
            console.log('添加小说评论 user/discuss');
            console.log(res);
            if (res.code == 1) {
                // 设置评论时间
                var timestamp = Date.parse(new Date());
                localStorage.setItem('timestamp', timestamp);

                showAlert({
                    text: 'แสดงความคิดเห็นสำเร็จ', //【必填】，否则不能正常显示
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
                console.log('添加小说评论user/discuss 错误');
                check_code(res.code);
            }


        },
        error: function(e) {
            console.log('添加小说评论user/discuss 请求失败');
        }
    });
}

// 添加漫画评论
function add_discuss_cartoon() {
    var content = $(".discuss_textarea").val();


    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "user/discuss",
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
            "cartoon_id": book_id,
            "star": star,
            "content": content
        },
        success: function(res) {
            console.log('添加漫画评论 user/discuss');
            console.log(res);
            if (res.code == 1) {
                // 设置评论时间
                var timestamp = Date.parse(new Date());
                localStorage.setItem('timestamp', timestamp);

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
                console.log('添加漫画评论user/discuss 错误');
                check_code(res.code);
            }


        },
        error: function(e) {
            console.log('添加漫画评论user/discuss 请求失败');
        }
    });
}


// 检查评论时间
function check_discuss_time() {
    //记录时间
    var time = parseInt(localStorage.getItem('timestamp'));
    //现在时间
    var timestamp = Date.parse(new Date());
    // 判断是否有记录时间
    if (time) {} else {
        localStorage.setItem('timestamp', timestamp);
    }
    // 判断时间是否超时
    var number = time + 60000;
    // 超时
    if (number >= timestamp) {
        showToast({
            text: 'คอมเม้นบ่อยเกินไป โปรดลองอีกครั้งในภายหลัง', //【必填】，否则不能正常显示 , 剩余的其他不是必填
            top: '45%', //toast距离页面底部的距离
            zindex: 999, //为了防止被其他控件遮盖，z-index默认为2
            speed: 500, //toast的显示速度
            time: 3000, //toast显示多久以后消失
            img: 'no'
        });
        return false;
    } else {
        // 没超时
        return true;
    }



}