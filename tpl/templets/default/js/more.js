// ____________________________________________________________________________________
//获取滚动条当前的位置
function getScrollTop() {
    var scrollTop = 0;
    if (document.documentElement && document.documentElement.scrollTop) {
        scrollTop = document.documentElement.scrollTop;
    } else if (document.body) {
        scrollTop = document.body.scrollTop;
    }
    return scrollTop;
}
//获取当前可视范围的高度  
function getClientHeight() {
    var clientHeight = 0;
    if (document.body.clientHeight && document.documentElement.clientHeight) {
        clientHeight = Math.min(document.body.clientHeight, document.documentElement.clientHeight);
    } else {
        clientHeight = Math.max(document.body.clientHeight, document.documentElement.clientHeight);
    }
    return clientHeight;
}
//获取文档完整的高度 
function getScrollHeight() {
    return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
}



// window.onscroll = function() {
//     if (getScrollTop() + getClientHeight() + 100 > getScrollHeight()) {

//         if (running) {
//             console.log('body下拉刷新了');
//             running = !running;
//             $('.more_loading').show();
//             setTimeout(function() {
//                 add();
//                 running = !running;
//             }, 1000)
//         }

//     }
// }

// ____________________________________________________________________________________
// 获取参数
var list_name = GetQueryString("list_name");
console.log(list_name);
// 名字
var type_name = '';


check_list_name();


// 检查参数名字
function check_list_name() {
    if (list_name == "free_read") {
        type_name = 'อ่านฟรี';
    } else if (list_name == "recommend_cartoon") {
        type_name = 'การ์ตูนแนะนำ';
    } else if (list_name == "hot_fiction") {
        type_name = 'นิยายขายดี';
    } else if (list_name == "hot_cartoon") {
        type_name = 'การ์ตูนฮอต';
    } else if (list_name == "phb") {
        type_name = 'จัดอันดับ';
    } else if (list_name == 'new_book') {
        type_name = 'ใหม่ล่าสุด';
    }
    document.title = type_name;
    $('.back_p').html(type_name);
    check_add();
}

// 检查执行什么类型接口
function check_add() {
    if (list_name == "free_read") {
        free_read();
    } else if (list_name == "recommend_cartoon") {
        recommend_cartoon();
    } else if (list_name == "hot_fiction") {
        hot_fiction();
    } else if (list_name == "hot_cartoon") {
        hot_cartoon();
    } else if (list_name == "phb") {
        hot_fiction();
    } else if (list_name == 'new_book') {
        new_book();
    }
}

// 免费阅读
function free_read() {
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
                var html = '';
                for (var i = 0; i < res.result.data.length; i++) {
                    var text = "";
                    if (res.result.data[i]["update_status"] == "2") {
                        text = 'จบแล้ว';
                    } else {
                        text = 'กำลังเชื่อมต่อ';
                    }
                    html += `<li>
                                <div class="book_list_css_2 go_fiction"
                                data-book_id="` + res.result.data[i]["book_id"] + `"
                                >
                                    <img src="` + res.result.data[i]["bpic"] + `" class="cursor cover" onerror="this.src='images/replace_2.png'">
                                    <div class="row">
                                        <p class="p1-hidden cursor">
                                        ` + res.result.data[i]["other_name"] + `
                                        </p>
                                        <p class="p1-hidden cursor">` + res.result.data[i]["writer_name"] + `</p>
                                        <p class="p2-hidden cursor">` + res.result.data[i]["desc"] + `</p>
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
                                </div>
                            </li>`;
                }
                // 清除
                $('.list li').remove();
                // 渲染
                $('.list ul').append(html);



            } else {
                console.log('免费阅读 book/get_freeList 错误');
            }



        },
        error: function(e) {
            console.log('免费阅读 book/get_freeList 请求失败');
        }
    });
}

// 推荐漫画
function recommend_cartoon() {
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
                var html = '';
                for (var i = 0; i < res.result.length; i++) {
                    var text = "";
                    if (res.result[i]["update_status"] == "2") {
                        text = 'จบแล้ว';
                    } else {
                        text = 'กำลังเชื่อมต่อ';
                    }
                    html += `<li>
                                <div class="book_list_css_2 go_cartoon"
                                data-cartoon_id="` + res.result[i]["cartoon_id"] + `"
                                >
                                    <img src="` + res.result[i]["bpic"] + `" class="cursor cover" onerror="this.src='images/replace_2.png'">
                                    <div class="row">
                                        <p class="p1-hidden cursor">
                                        ` + res.result[i]["other_name"] + `
                                        </p>
                                        <p class="p1-hidden cursor">` + res.result[i]["writer_name"] + `</p>
                                        <p class="p2-hidden cursor">` + res.result[i]["desc"] + `</p>
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
                                </div>
                            </li>`;
                }
                // 清除
                $('.list li').remove();
                // 渲染
                $('.list ul').append(html);



            } else {
                console.log('推荐漫画 cartoon/get_cartoonList 错误');
            }



        },
        error: function(e) {
            console.log('推荐漫画 cartoon/get_cartoonList 请求失败');
        }
    });
}


// 热门小说
function hot_fiction() {
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
            console.log('热门小说 book/get_bookList');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                for (var i = 0; i < res.result.length; i++) {
                    var text = "";
                    if (res.result[i]["update_status"] == "2") {
                        text = 'จบแล้ว';
                    } else {
                        text = 'กำลังเชื่อมต่อ';
                    }
                    html += `<li>
                                <div class="book_list_css_2 go_fiction"
                                data-book_id="` + res.result[i]["book_id"] + `"
                                >
                                    <img src="` + res.result[i]["bpic"] + `" class="cursor cover" onerror="this.src='images/replace_2.png'">
                                    <div class="row">
                                        <p class="p1-hidden cursor">
                                        ` + res.result[i]["other_name"] + `
                                        </p>
                                        <p class="p1-hidden cursor">` + res.result[i]["writer_name"] + `</p>
                                        <p class="p2-hidden cursor">` + res.result[i]["desc"] + `</p>
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
                                </div>
                            </li>`;
                }
                // 清除
                $('.list li').remove();
                // 渲染
                $('.list ul').append(html);



            } else {
                console.log('热门小说 book/get_bookList 错误');
            }



        },
        error: function(e) {
            console.log('热门小说 book/get_bookList请求失败');
        }
    });
}

// 热门漫画
function hot_cartoon() {
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
            console.log('热门漫画 cartoon/hot_cart_groom');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                for (var i = 0; i < res.result.length; i++) {
                    var text = "";
                    if (res.result[i]["update_status"] == "2") {
                        text = 'จบแล้ว';
                    } else {
                        text = 'กำลังเชื่อมต่อ';
                    }
                    html += `<li>
                                <div class="book_list_css_2 go_cartoon"
                                data-cartoon_id="` + res.result[i]["cartoon_id"] + `"
                                >
                                    <img src="` + res.result[i]["bpic"] + `" class="cursor cover" onerror="this.src='images/replace_2.png'">
                                    <div class="row">
                                        <p class="p1-hidden cursor">
                                        ` + res.result[i]["other_name"] + `
                                        </p>
                                        <p class="p1-hidden cursor">` + res.result[i]["writer_name"] + `</p>
                                        <p class="p2-hidden cursor">` + res.result[i]["desc"] + `</p>
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
                                </div>
                            </li>`;
                }
                // 清除
                $('.list li').remove();
                // 渲染
                $('.list ul').append(html);



            } else {
                console.log('热门漫画 cartoon/hot_cart_groom 错误');
            }



        },
        error: function(e) {
            console.log('热门漫画 cartoon/hot_cart_groom 请求失败');
        }
    });
}

// 新书速递
function new_book() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "book/new",
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
            console.log('新书速递 book/new');
            console.log(res);

            if (res.code == "1") {
                var html = '';
                for (var i = 0; i < res.result.length; i++) {
                    var text = "";
                    if (res.result[i]["update_status"] == "2") {
                        text = 'จบแล้ว';
                    } else {
                        text = 'กำลังเชื่อมต่อ';
                    }
                    html += `<li>
                                <div class="book_list_css_2 go_fiction"
                                data-book_id="` + res.result[i]["book_id"] + `"
                                >
                                    <img src="` + res.result[i]["bpic"] + `" class="cursor cover" onerror="this.src='images/replace_2.png'">
                                    <div class="row">
                                        <p class="p1-hidden cursor">
                                        ` + res.result[i]["other_name"] + `
                                        </p>
                                        <p class="p1-hidden cursor">` + res.result[i]["writer_name"] + `</p>
                                        <p class="p2-hidden cursor">` + res.result[i]["desc"] + `</p>
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
                                </div>
                            </li>`;
                }
                // 清除
                $('.list li').remove();
                // 渲染
                $('.list ul').append(html);



            } else {
                console.log('新书速递 book/new 错误');
            }



        },
        error: function(e) {
            console.log('新书速递 book/new 请求失败');
        }
    });
}