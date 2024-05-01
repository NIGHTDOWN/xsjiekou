$('html').css('font-size', document.body.clientWidth / 7.5 + 'px');

//获取参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}
// 获取网址上参数
var articleid = GetQueryString("articleid");
var uid = GetQueryString("uid");
var token = GetQueryString("token");
var readid = $(".title").attr("readid");

// 设置时间
var time = parseInt($(".title").attr("endtime") * 60);
// var time = parseInt(2);
// 是否显示阅读全部
var read_all = false;
// 是否执行中
var running = true;


// 点击阅读全文
$('.remove_max_height').on('click', function() {
    $('.list').removeClass('max-height');
    $(this).hide();
    read_all = true;
});
// 设置图片宽度
var length = $('.list img').length;
for (var i = 0; i < length; i++) {
    $('.list img').eq(i).attr("width", " ");
    $('.list img').eq(i).attr("height", " ");
}
// 设置文字大小
$('.list p').css({ "font-size": '0.3rem' });

// 定时器
var int = self.setInterval("clock()", 1000);

function clock() {
    // console.log(time);
    if (time > 0) {
        time--;
    } else {
        clearInterval(int);
        set_clock_ajax();
    }
}





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

function set_clock_ajax() {
    window.onscroll = function() {
        // 下拉
        if (getScrollTop() + getClientHeight() + 50 > getScrollHeight()) {

            console.log('下拉刷新了');
            // 是否有token
            if (token) {

                console.log("token");




                // 是否展示了所以页面
                if (read_all) {
                    console.log("read_all");
                    // 是否执行过了
                    if (running) {

                        running = !running;
                        console.log("running");
                        //此处发起AJAX请求
                        $.ajax({
                            type: "post",
                            url: "http://192.168.6.69/api/task/end",
                            beforeSend: function(request) {
                                request.setRequestHeader("uid", uid);
                                request.setRequestHeader("token", token);
                            },
                            dataType: 'json',
                            data: {
                                articleid: articleid,
                                uid: uid,
                                readid: readid
                            },
                            success: function(data) {
                                console.log(data);
                            },
                            error: function(e) {
                                // alert("错误！！");
                            }
                        });
                    }


                }
            }
        }
    }
}