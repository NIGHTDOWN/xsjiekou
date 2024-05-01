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

var no_data = `<div class="flexCenter flexCenter-column no_data">
            <img src="images/ss_findout@2x.png" alt="" >
            <p>ไม่พบเนื้อหาที่เกี่ยวข้อง<br/>
            ลองใช้คำหลักอื่น</p>
        </div>`;

// 清空搜索框
$('.search_text_div input').val('');
$('.search_input_clear').hide();

get_hot();
// 热门搜索
function get_hot() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "book/get_randList",
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
            console.log('热门搜索 book/get_randList');
            console.log(res);
            if (res.code == "1") {
                var result = res.result;
                var html = '';
                for (var i = 0; i < result.length; i++) {
                    html += `<div class="hot_lists go_fiction" data-book_id="` + result[i]["book_id"] + `">
                    <div class="flexCenter-ai-center flex-wrap-nowrap w-100">
                        <img src="` + result[i]["bpic"] + `" class="cursor book_show_cover"  onerror="this.src='images/replace_2.png'">
                        <div class="max_5">
                            <p class="book_name p1-hidden cursor">` + result[i]["other_name"] + `</p>
                            <p class="book_desc p1-hidden cursor">` + result[i]["desc"] + `</p>
                        </div>
                    </div>
                </div>`;
                }
                $('.hot_lists').remove();
                $('.hot_list').append(html);

            } else {
                console.log('热门搜索 book/get_randList 错误');
            }


            load_end();

        },
        error: function(e) {


            console.log('热门搜索 book/get_randList 请求失败');
            $('.hot').hide();
            load_end();
        }
    });
}


// 监听搜索
$('#searchPageText').bind('input propertychange', function() {

    var keyword = $('#searchPageText').val();
    if (keyword) {
        if (running) {
            running = !running;
            console.log("搜索框有字符串 " + keyword);
            $('.search_input_clear').show();
            // $('.hot').hide();
            // running = !running;
            book_search(keyword);

        }


    } else {
        console.log("搜索为null");
        $('.book_search_page').hide();
        $('.hot').show();
        $('.search_input_clear').hide();
    }


})




// 搜索书籍
function book_search(keyword) {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "common/book_search",
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
            "keyword": keyword
        },
        success: function(res) {
            console.log('搜索书籍 common/book_search');
            console.log(res);
            if (res.code == "1") {
                $('.book_search_book div').remove();
                $('.book_search_cartoon div').remove();
                // 数组
                var result = res.result;
                if (result.length > 0) {
                    // 小说
                    var book_html = '';
                    var book_number = 0;
                    // 漫画
                    var cartoon_html = '';
                    var cartoon_number = 0;

                    for (var i = 0; i < result.length; i++) {

                        if (result[i]["type"] == 1) {
                            // 这里是小说
                            book_html += `<div class="lists go_fiction" data-book_id="` + result[i]["book_id"] + `">
                                            <div class="flexCenter-ai-center flex-wrap-nowrap w-100">
                                                <img src="` + result[i]["bpic"] + `" class="cursor book_show_cover"  onerror="this.src='images/replace_2.png'">
                                                <div class="max_5">
                                                    <p class="book_name p1-hidden cursor">` + result[i]["other_name"] + `</p>
                                                    <p class="book_desc p1-hidden cursor">` + result[i]["desc"] + `</p>
                                                </div>
                                            </div>
                                        </div>`;
                            book_number++;


                        } else if (result[i]["type"] == 2) {
                            // 这里是漫画
                            cartoon_html += `<div class="lists go_cartoon" data-cartoon_id="` + result[i]["book_id"] + `">
                                            <div class="flexCenter-ai-center flex-wrap-nowrap w-100">
                                                <img src="` + result[i]["bpic"] + `" class="cursor book_show_cover"  onerror="this.src='images/replace_2.png'">
                                                <div class="max_5">
                                                    <p class="book_name p1-hidden cursor">` + result[i]["other_name"] + `</p>
                                                    <p class="book_desc p1-hidden cursor">` + result[i]["desc"] + `</p>
                                                </div>
                                            </div>
                                        </div>`;
                            cartoon_number++;
                        }
                    }

                    // 小说渲染
                    if (book_number > 0) {
                        $('.book_search_book').append(book_html);
                    } else {
                        $('.book_search_book').append(no_data);
                    }

                    // 漫画渲染

                    if (cartoon_number > 0) {
                        $('.book_search_cartoon').append(cartoon_html);
                    } else {
                        $('.book_search_cartoon').append(no_data);
                    }




                } else {

                    $('.book_search_book').append(no_data);



                    $('.book_search_cartoon').append(no_data);

                }

            } else {
                console.log('搜索书籍 common/book_search 错误');
            }


            running = !running;
            $('.hot').hide();
            $('.book_search_page').show();
        },
        error: function(e) {
            console.log('搜索书籍 common/book_search 请求失败');
            running = !running;
            $('.hot').hide();
            $('.book_search_page').show();
        }
    });
}



// 查看小说
$('.book_search_book_button').on('click', function() {
    $('.book_search_cartoon').hide();
    $('.book_search_book').show();
    $('.book_search_head div').removeClass('on');
    $(this).addClass('on');
});

// 查看漫画
$('.book_search_cartoon_button').on('click', function() {
    $('.book_search_book').hide();
    $('.book_search_cartoon').show();
    $('.book_search_head div').removeClass('on');
    $(this).addClass('on');
});


// 删除搜索框内容
$('.search_input_clear').on('click', function() {

    $('.search_text_div input').val('');
    console.log("搜索为null");
    $('.book_search_page').hide();
    $('.hot').show();
    $('.search_input_clear').hide();
});