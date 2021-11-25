
//滚动脚本

var nScrollHight = 0;
var lock = false;
var readlink = "lookstory://com.ng.story/read?bookid=&type=2&secid=";
var playlink = "https://play.google.com/store/apps/details?id=com.ng.story";
var secid = '18100';
var bookid = '53683';
var type = 1;
var showdload = false;
DEBUG = true;
function down2() {
    _go_url(playlink);
}
function down() {
    _go_url('https://bc-video-h5.oss-cn-hongkong.aliyuncs.com/test/app-release.apk');
}
function showloadbox2() {

    $('.bottom_download').slideUp();
    // $('.mask').slideUp();
    $('.mask').animate({
        top: "0px"
    }, 1000);
}
function _openAppUrl(appUrl) {
    var ua = navigator.userAgent.toLocaleLowerCase(),
        openBrowser = null,
        deviceVersion = 0,
        matchVersion = null,
        openAppType = "",
        downLoadUrl = playlink;

    //如果是在微信内部点击的时候
    if (ua.indexOf("micromessenger") != -1) {
        _openAppUrl = function () {
            // alert("DEMO，请在移动端的浏览器查看！");
            alert("请在浏览器打开!");
        }
    } else {
        //在浏览器打开，判断是在移动端还是在PC端
        if (matchVersion = navigator.userAgent.match(/OS\s*(\d+)/)) {
            //赋值，并且判断
            //IOS设备的浏览器
            deviceVersion = matchVersion[1] || 0;

            if (deviceVersion - 9 >= 0) {
                openAppType = "newType";
            }
        } else if (matchVersion = navigator.userAgent.match(/Android\s*(\d+)/)) {
            //Android的设备
            deviceVersion = matchVersion[1] || 0;

            if (deviceVersion - 5 >= 0) {
                openAppType = "newType";
            }

        } else {
            //PC端的设备
            openAppType = "pc";
        }


        if (openAppType == "pc") {
            _openAppUrl = function () {
                alert("请在浏览器打开!");
            }
        } else if (openAppType == "newType") {
            //使用新的方法，尝试打开APP
            //IOS>9,Android>5的版本

            _openAppUrl = function (url) {
                // alert("โปรดติดตั้งแอปก่อนโปรดติดตั้งแอพก่อนดำเนินการต่อ");
                // down();
                //复制口令
                // copycode();

                var history = window.history,
                    body = $("body").eq(0),
                    ifr = $('<iframe class = "full-screen dn" style = "z-index:101;border:none;width:100%;height:100%;" src="' + downLoadUrl + '"></iframe>');

                body.append(ifr);
                $(window).on("popstate", function (e) {
                    var state = history.state;

                    if (!state) {
                        ifr.addClass("dn");
                    }
                });

                function _show() {
                    history.pushState({}, "DownPage", "");
                    ifr.removeClass("dn");
                }

                _openAppUrl = function (url) {
                    location.href = url;
                    _show();
                }

                _openAppUrl(url);

            }
        } else {
            //使用计算时差的方案打开APP
            var checkOpen = function (cb) {
                var _clickTime = +(new Date()),
                    _count = 0,
                    intHandle = 0;

                //启动间隔20ms运行的定时器，并检测累计消耗时间是否超过3000ms，超过则结束
                intHandle = setInterval(function () {
                    _count++;
                    var elsTime = +(new Date()) - _clickTime;

                    if (_count >= 100 || elsTime > 3000) {
                        clearInterval(intHandle);
                        //计算结束，根据不同，做不同的跳转处理，0表示已经跳转APP成功了
                        if (elsTime > 3000 || document.hidden || document.webkitHidden) {
                            cb(0);
                        } else {
                            cb(1);
                        }

                    }
                }, 20);
            }

            _openAppUrl = function (url) {
                var ifr = document.createElement('iframe');

                ifr.src = url;
                ifr.style.display = 'none';

                checkOpen(function (opened) {
                    if (opened === 1) {
                        location.href = downLoadUrl;
                    }
                });

                document.body.appendChild(ifr);

                setTimeout(function () {
                    document.body.removeChild(ifr);
                }, 2000);
            }

        }

    }
    _openAppUrl(appUrl);
}

function showloadbox4() {
    _openAppUrl(readlink + secid);
}
function close1() {

    $('.mask').animate({
        top: $(window).height() + 100
    },
        1000);
    $('.bottom_download').slideDown();
}

function loading() {
    if (showdload) return false;
    if (lock) return false;
    lock = true;
    yAjax('http://' + window.location.host + '/api/cartoon/get_wapcontent', {
        'cartoon_id': bookid,
        'cart_section_id': secid
    }, function (data) {
        lock = false;

        if (data['code'] == 1) {
            var ret = data['result'];
            secid = ret['next'];
            var imgs = '';
            if (ret['images']) {
                $.each(ret['images'], function (i, v) {
                    imgs += " <img src='" + v['url'] + "' class='cursor' onerror=\"this.src='/tpl/templets/default/images/replace_2.png'\">";
                });
            }


            // var html = "<p>" + ret['title'] + "</p>" + ret['sec_content'];
            var html = "<div class='csec'>" + imgs + "</div>";
            $('.content').append(html);
        } else {
            showdload = true;
        }
    });
}
//显示下载遮罩
function showloadbox() {
    if (!showdload) return false;
    $('.bottom_download').slideUp();
    // $('.mask').slideUp();

    $('.mask').animate({
        top: "0px"
    }, 1000);
}

function closebar() {
    $('.guide-hd-banner').hide();
}

$(document).ready(function () {

    $('.intro-text').on('click', function () {

        var e = $("#js_toggle_desc");
        var a = $("#js_desc_content");
        e.hasClass("ift-down");
        e.toggleClass("arrow-reverse");
        a.toggleClass("spread-text");
    });
    $('.comic-chapter').on('click', function () {

        var t = $('.chapter-rank')
            , a = t.hasClass("asc");
        var t2 = $('#js_chapters')
            , a2 = t2.hasClass("asc");
        var e = $('#js_chapters');
        // $("#js_chapter_list li").removeClass("hide"),
        a ? t.removeClass("asc").addClass("desc") : t.removeClass("desc").addClass("asc");
        // a2 ? t2.removeClass("asc").addClass("desc") : t2.removeClass("desc").addClass("asc");
        // e.$chapterList.sort(function (t, e) {
        //     t = $(t).data("index"),
        //         e = $(e).data("index");
        //     return a ? e - t : t - e
        // }),
        //     e.$chapterList.detach().appendTo(l("#js_chapters")),
        //     e.$chapterRank.find(".btn-reverse").toggleClass("btn-reverse-change")
        // $o = [];
        // $(e.find("li").toArray().reverse()).each(function (index, item) {
        //     // var text = $(item).text() + " + " + index;
        //     // $(item).text(text);
        //     $o[index]=
        // });
        $b = e.find("li").toArray().reverse();
        e.html(); e.append($b);
    });
    $('.more-chapter-btn').on('click', function () {
        var t = this
            , e = $(".js_more_chapters")
            , a = $(".js_up_chapterList")
            , i = $("#js_chapters")
            , n = $(".list-tucao-num")
            , s = $(".js_collect_num").text()
            , o = $(".tucao span").html();
        i.toggleClass("expand"),
            e.hide(),
            a.show()


    });
    $('.close-content').on('click', function () {
        e = $(".js_more_chapters")
            , a = $(".js_up_chapterList"),
            i = $("#js_chapters"),
            i.toggleClass("expand"),
            e.show(),
            a.hide();
    });
});
