var running = true;

// 总问题的长度 -1 从0开始
var page_length;

// 当期是第几题
var now_length = 0;

// 获取用户ID
var users_id = GetQueryString("uid");
var openType = GetQueryString("openType");
// alert("第一个——参数uid="+users_id);

if (!users_id) {
    users_id = GetQueryString("users_id");
}
// alert("users_id"+users_id);



// 图片加载load
function imgLoad() {
    var time = '';
    var img_ = [
        'images/question/button_click.png'



    ];
    imgLoader(img_, function(percentage) {
        var percentT = percentage * 100;

        if (percentage == 1) {

            get_question();

        }
    });
}
imgLoad();



// get_question();

function get_question() {

    var data = get_ajax_header();
    $.ajax({
        type: "post",
        url: api_url + "question/get_question",
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
            console.log('获取题目信息 question/get_question');
            console.log(res);
            if (res.code == "1") {
                set_question(res.result);
            } else {
                console.log('获取题目信息 question/get_question 错误');
                check_code(res.code);
            }

        },
        error: function(e) {
            console.log('获取题目信息 question/get_question 请求失败');
            // alert("เกิดความผิดพลาด โปรดรีเฟรช");
        }
    });
}





// 初始化问题列表
// set_question(data);


function set_question(arr) {
    page_length = arr.length - 1;
    var html = '';
    for (var i = 0; i <= arr.length - 1; i++) {
        console.log(i);

        var question_type = arr[i]["question_type"]
        switch (question_type) {
            case "1":
                var answer = arr[i]["answer"];

                var list = '';
                for (var k = 0; k < answer.length; k++) {
                    list += `<li class="lists flexCenter-ai-center cursor" data-answer_title="` + answer[k]["answer_title"] + `" data-answer_id="` + answer[k]["answer_id"] + `" data-answer_option="` + answer[k]["answer_option"] + `">
                                <div class="icon_page">
                                    <img src="images/question/choose_click.png" class="click_icon">
                                </div>
                                <p class="cursor">` + answer[k]["answer_title"] + `</p>
                            </li>`;
                }

                html += `<div class="page_` + i + ` question_lists" data-question_id="` + arr[i]["question_id"] + `" data-question_type="` + arr[i]["question_type"] + `">
                            <div class="title_page">
                                <p>` + arr[i]["question_title"] + `</p>
                            </div>
                            <div class="list_page">
                                <ul class="list">
                                ` + list + `
                                </ul>
                            </div>
                        </div>`;


                break;
            case "2":
                var answer = arr[i]["answer"];


                html += `<div class="page_` + i + ` question_lists" data-question_id="` + arr[i]["question_id"] + `" data-question_type="` + arr[i]["question_type"] + `">
                            <div class="title_page">
                                <p>` + arr[i]["question_title"] + `</p>
                            </div>
                            <div class="list_page" contenteditable='true' style="text-align: center;">
                                <textarea type='text' maxlength="70"></textarea>
                            </div>
                        </div>`;

                break;
        }
    }
    // console.log(html);
    $('.question_list').append(html);
    $('.question_lists').eq(0).show();
    check_now_length();
    line();
    $('.loading_page').fadeOut();

    // 监听搜索
    $('textarea').bind('input propertychange', function() {
        // console.log("???");
        textareaH($(this));
        check_answer();
    })
}

$('textarea').on('input propertychange',function(){
  textareaH($(this));
  console.log(1);
});

function textareaH(user){
  let obj = user,
      len = obj.length;
  for (let i = 0; i < len; i++) {
      // console.log(obj[i]);
      obj[i].style.height = 'auto';
      obj[i].style.height = obj[i].scrollHeight + 'px';
  }
}



// 选择题选择答案
$('.question_list').on('click', '.lists', function(event) {
    if ($(this).hasClass('lists_on')) {
        $(this).removeClass('lists_on');

    } else {
        $(this).addClass('lists_on');

    }
    check_answer();

});

// 隐藏全部按钮
function bottom_button_hide() {
    $('.previous_button').hide();
    $('.next_button').hide();
    $('.send_button').hide();
}
// 下一题
$('.next_button').on('click', function() {
    if ($(this).hasClass('can_click')) {
        $(this).removeClass('can_click');
        $('.page_' + now_length).hide();
        bottom_button_hide();
        now_length = now_length + 1;
        $('.page_' + now_length).show();
        check_now_length();
        line();
        check_answer();
    } else {
        showToast({
            text: 'โปรดตอบคำถาม',
            top: '45%',
            zindex: 9999,
            speed: 500,
            time: 3000,
            img: 'no'
        });

    }
});

// 上一题
$('.previous_button').on('click', function() {
    if ($(this).hasClass('can_click')) {
        $('.page_' + now_length).hide();
        bottom_button_hide();
        now_length = now_length - 1;
        $('.page_' + now_length).show();
        check_now_length();
        line();
        check_answer();
    }
});


// 下方进度条
function line() {
    var all_length = page_length + 1;
    // console.log("总共有" + all_length);
    // var length = 100 / all_length;
    // var line_length = (now_length + 1) * length;
    // console.log(now_length);
    // $('.line').css({ 'width': line_length + '%' });
    $('.line').html((now_length + 1) + `/` + all_length);
    var question_type = $('.page_' + now_length).data("question_type");
    if (question_type == "1") {
        $('.type').html("เลือกคำตอบ");
    } else if (question_type == "2") {
        $('.type').html("แบบสอบถาม");
    }

}





// 检查现在是第几题然后设置下方按钮
function check_now_length() {
    if (now_length == 0) {
        // $('.previous_button').show();
        $('.next_button').show();
    } else if (now_length > 0 && now_length < page_length) {
        $('.previous_button').show();
        $('.next_button').show();
    } else if (now_length >= page_length) {
        $('.previous_button').show();
        $('.send_button').show();
    }
}
// 检查是否有答案
function check_answer() {
    var question_type = $('.page_' + now_length).data("question_type");
    if (question_type == 1) {
        // 多选题
        var answer_length = $('.page_' + now_length + ' .lists_on').length;
        if (answer_length > 0) {
            // 有答案
            if (now_length == page_length) {
                // 已经是最后一题
                $('.send_button').addClass('can_click');
            } else {
                $('.next_button').addClass('can_click');

            }
        } else {
            // 没答案
            if (now_length == page_length) {
                // 已经是最后一题
                $('.send_button').removeClass('can_click');
            } else {
                $('.next_button').removeClass('can_click');

            }
        }
    } else if (question_type == 2) {
        var val = $('.page_' + now_length + ' textarea').val();
        if (val) {
            // 有答案
            if (now_length == page_length) {
                // 已经是最后一题
                $('.send_button').addClass('can_click');
            } else {
                $('.next_button').addClass('can_click');

            }
        } else {
            // 没答案
            if (now_length == page_length) {
                // 已经是最后一题
                $('.send_button').removeClass('can_click');
            } else {
                $('.next_button').removeClass('can_click');

            }
        }
    }


}






$('.send_button').on('click', function() {
    if ($(this).hasClass('can_click')) {
        if (running) {
            // alert("running="+running);

            running = false;
            send_answer();
        }

    }

});

var send_the_answer = [];

function send_answer() {
    // alert("send_answer");
    for (var i = 0; i <= page_length; i++) {
        // 获取题目的类型
        var question_type = $('.page_' + i).data('question_type');
        //新建一个对象
        var html = {};
        // 题目类型
        html.question_type = question_type;
        // 题目ID
        html.question_id = $('.page_' + i).data('question_id');
        // 题目的问题
        html.question_title = $('.page_' + i + ' .title_page p').html();

        //


        switch (question_type) {
            case 1:
                // 多选题 选择答案的长度
                var lists_on_length = $('.page_' + i + ' .lists_on').length;
                // 新建一个数组记录选择答案
                var answer_option_arr = [];
                var answer_title_arr = [];
                var answer_id_arr = [];
                var answer_all = [];
                for (var k = 0; k < lists_on_length; k++) {
                    // 把选择的答案选项加入数组
                    var answer_option = $('.page_' + i + ' .lists_on').eq(k).data('answer_option');
                    answer_option_arr.push(answer_option);

                    // 把选择的答案标题加入数组
                    var answer_title = $('.page_' + i + ' .lists_on').eq(k).data('answer_title');
                    answer_title_arr.push(answer_title);

                    //把答案ID加入数组
                    var answer_id = $('.page_' + i + ' .lists_on').eq(k).data('answer_id');
                    answer_id_arr.push(answer_id);

                    var b = {};
                    b.answer_option = answer_option;
                    b.answer_title = answer_title;
                    b.answer_id = answer_id;
                    // 题目ID
                    // console.log($('.page_' + i).data('question_id'));
                    answer_all.question_id = $('.page_' + i).data('question_id');
                    answer_all.push(b);

                }
                // 数组加入当前对象
                // html.answer_option_list = answer_option_arr;

                // html.answer_title_list = answer_title_arr;
                // html.answer_id_list = answer_id_arr;
                // 输入文本为空
                html.answer_desc = "";
                html.answer_all = answer_all;
                break;
            case 2:
                // 把输入文本加入对象
                html.answer_desc = $('.page_' + i + ' textarea').val();
                // 多选为空
                // html.answer_option_list = [];

                // html.answer_title_list = [];
                // html.answer_id_list = [];

                // var answer_all = [];

                // var b = {};
                // // 题目ID
                // b.question_id = '';
                // answer_all.push(b);
                // html.answer_all = answer_all;
                break;
        }
        // 加入数组
        send_the_answer.push(html);

    }
    send(send_the_answer);
    // console.log(send_the_answer);
    send_the_answer = [];
}


function send(answer_arr) {
    // alert("send");
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        // https://apikorsea.qrxs.cn/api/common/question_answer
        // url: api_url + "common/question_answer",
        url: api_url + "question/answer",
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
            "answer_arr": answer_arr,
            "users_id": users_id
        },
        success: function(data) {
            console.log('提交问卷 question/answer');
            console.log(data);
            // alert("data.code="+data.code)
            if (data.code == "1") {
                get_question_coin();
            } else {
                running = true;
                $('.TS_no_page').fadeIn();
                setTimeout(function() {
                    $('.TS_no_page').fadeOut();
                }, 2000)
                console.log('提交问卷 question/answer 错误');
            }

        },
        error: function(e) {
            // alert("send出错");
            running = true;
            console.log('提交问卷 question/answer 请求失败');
            // alert("เกิดความผิดพลาด โปรดรีเฟรช");
        }
    });
}


// 获取奖励金币的数量
function get_question_coin() {
    var data = get_ajax_header();

    $.ajax({
        type: "post",
        url: api_url + "question/get_question_coin",
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
        success: function(data) {
            console.log('奖金金额 question/get_question_coin ');
            console.log(data);
            if (data.code == "1") {
                running = true;
                $('.add_gold span').html("+" + data.result.question_coin);
                $('.TS_yes_page').fadeIn();
            } else {
                running = true;
                $('.TS_no_page').fadeIn();
                setTimeout(function() {
                    $('.TS_no_page').fadeOut();
                }, 2000)
                console.log('奖金金额 question/get_question_coin 错误');
            }

        },
        error: function(e) {
            running = true;
            console.log('奖金金额 question/get_question_coin 请求失败');
            // alert("เกิดความผิดพลาด โปรดรีเฟรช");
        }
    });
}

$('.TS_yes_button').on('click', function(event) {
    if (openType == "web") {
        goBack();
    } else {
        location.href = "question://finish=1";
    }


});