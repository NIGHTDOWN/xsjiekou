var nScrollHight = 0;
var lock = false;
var nows = 0;
var showdload = false;
var scrollTop, ks_area;
var titlelock = false;
var lastpointy = 0;
var autopayindex = 'autopay';
//加載購買模塊，
function loadpay() {
    if (!needpay) return false;

    $ob = getnowob();
    $title = $ob.attr('chapter-name');
    $id = $ob.attr('chapter-id');
    $coin = $ob.attr('chapter-coin');
    initcash($coin);
    $autopay = isautopay();
    if ($autopay) {
        $('#js_autoBuy').addClass('active');
    } else {
        $('#js_autoBuy').removeClass('active');
    }

    $('#js_payChapterContent').text($title);
    // d($coin);
    $('.paycash').text($coin);

    $('#js_payChapterContent').attr('chapter-id', $id);
    $('#js_payChapter').show();
}
//获取钱包
function initcash($coin) {
    yAjax(wallerurl, {}, function(data) {
        if (data.code == 1) {
            $walvar = data.result;
            if ($walvar.golden_bean < $coin && $walvar.remainder < $coin) {
                //显示充值按钮
                $('.pay-btn-box .js_goPay').hide();
                $('.pay-btn-box .js_recharge').show();
            } else {
                //显示解锁按钮
                $('.pay-btn-box .js_goPay').show();
                $('.pay-btn-box .js_recharge').hide();
            }
            $('.cbt3').text($walvar.golden_bean);
            $('.cbt6 ').text($walvar.remainder);
            return true;
        }
        return false;
    })
}

function loadpay2() {

    $ob = getnowob();
    needpay = parseInt($ob.attr('needpay'));
    loadpay();
}

function hidepay() {

    $('#js_payChapter').hide();
}

function hidecomment() {
    $('.comment-textarea').val('');
    $('#js_comment').hide();
    $('.comment-textarea-box-bd').removeClass('hover');
}

function showcomment() {
    hidecate();
    $('#js_comment').show();
}

//提交评论
function subcomment() {
    $dstr = $('.comment-textarea').val();

    if ($dstr == '') {

        $('.comment-textarea').focus();
        $('.comment-textarea-box-bd').addClass('hover');
        return false;
    }
    $ob = getnowob();
    $data = {
        'bookid': bookid,
        'type': type,
        'star': 5,
        'section_id': $ob.attr('chapter-id'),
        'content': $dstr

    };
    hidecomment();
    yAjax(commenturl, $data, function(data) {
        if (data.code == 1) {
            showd(data.result);
        } else {
            showd(data.msg);

        }
    });
    // d(3);
    // d(Sdstr);

    //获取提交就内容

}

function fn() {
    $arr = $('.acgn-reader-chapter__item-box').find('.loading');
    $arr.removeClass('loading');
    $arr.each(function(i, v) {
        v = $(v);
        $str = "<img width='100%' src='" + v.attr('dataurl') + "'/>";
        var ob;
        ob = $($str);
        // d("<img src='" + v.attr('dataurl') + "' />");
        ob[0].onload = function() {
            // d(v.attr('chapter-index'));
            // d(v.find('.acgn-reader-chapter__loading-tip'));
            v.find('.acgn-reader-chapter__loading-tip').remove();
            // d(ob.attr('src'));
            v.prepend(ob);
        };
    });

    return 1;
}
//目录生产缓存
function catecache() {
    $index = "cate." + uid + '.' + bookid + '.' + type;
    $cate = loadcate(1);
    setStorage($index, $cate, 1800);
    return $cate;
}

function changecache($list_order) {
    $cache = getcatechache();
    $data = $cache[$list_order];
    if ($data) {
        $index = "cate." + uid + '.' + bookid + '.' + type;
        $cache[$list_order]['ispay'] = 1;
        setStorage($index, $cache, 1800);
    } else {
        //重新网络缓存
        catecache();
    }

}

function getcatechache() {
    $index = "cate." + uid + '.' + bookid + '.' + type;
    $cate = getStorage($index);
    if (!$cate) {
        $cate = catecache();
    }
    return $cate;
}

function cate() {
    $cate = getcatechache();
    initcate($cate);
    $('.read-category-dialog').show();
    gocate();
    //生成列表
}

function hidecate() {
    $('.read-category-dialog').hide();
}

function gochapter($id) {
    hidecate();
    if ($id) {
        $url = pgurl + "?id=" + $id;
        _go_url($url);
    }
}

function initcate($cate) {
    $('.chapter-item-wrap.tmp').siblings().remove();
    $ob = $('.chapter-item-wrap.tmp').clone().removeClass('tmp').show();
    // $ob.css({ 'display': 'flex' });
    $list = $('.read-category-chapters');

    if ($cate) {
        $num = $cate[$cate.length - 1]['list_order'];
        $('.chapter-count').find('.num').text($num);

        $.each($cate, function(i, v) {
            $tmp = $ob.clone();
            $tmp.find('.chapter-item').text(v.title);
            $tmp.attr('chapter-id', v.section_id);
            $tmp.attr('id', 'chapter' + v.section_id);
            if (v.isfree == 0) {
                $tmp.find('i').remove();
            } else {
                if (v.ispay == 1) {
                    $tmp.find('i').addClass('ifst-unlock').removeClass('ifst-lock');
                } else {
                    $tmp.find('i').removeClass('ifst-unlock').addClass('ifst-lock');
                }

            }
            $list.append($tmp);

        });

    }

}
//滚动到对应章节
function gocate() {

    $t = getnowob();
    $id = '#chapter' + $t.attr('chapter-id');
    $('.chapter-item-wrap.active').removeClass('active');
    $ob = $($id).addClass('active');
    _go_url($id);
}

function savecate() {
    $index = "cate." + uid + '.' + bookid + '.' + type;
    $cate = getStorage($index);
    if (!$cate) {
        $cate = loadcate(0);
        setStorage($index, $cate, 1800);
    }
}

function isautopay() {
    $data = getCookie(autopayindex);
    if ($data == null) {
        return 0;
    }
    if ($data == 0) {
        return 0;
    }

    return $data;
}
$(function() {

    fn();
    savecate();
    $('.acgn-reader-chapter__scroll-box').click(function(event) {

        $y = event.clientY;

        $ymax = window.innerHeight;
        $y3 = $ymax / 3;

        $yc = 2;
        if ($y < $y3) {
            $yc = 1;
        } else if ($y >= $y3 && $y <= 2 * $y3) {
            $yc = 2;
        } else if ($y > 2 * $y3) {
            $yc = 3;
        }
        switch ($yc) {
            case 1:
                //向上滚动
                $(".acgn-reader-chapter").animate({ 'scrollTop': "-=" + $y3 + "px" }, 300);
                break;
            case 2:
                //弹出菜单
                menu();
                break;
            case 3:
                //向下滚动
                $(".acgn-reader-chapter").animate({ 'scrollTop': "+=" + $y3 + "px" }, 300);
                break;
        }
    });
    //目录按钮
    $('#js_ftMenuBtn').on('click', function() {
        menu();
        //先加载目录
        cate();

    });
    $(".comment-textarea-box-bd").hover(
        function() {
            $(this).addClass("hover");
        },
        function() {
            $(this).removeClass("hover");
        }
    );
    $('.read-category-ctrls').on('click', function() {

        $child = $(this).find('.ifst-caret-top');
        if ($child.hasClass('active')) {
            $child.removeClass('active').siblings().addClass('active');
            $('.read-category-chapters').css({ 'flex-direction': 'column-reverse' });
            //正序
        } else {
            $child.addClass('active').siblings().removeClass('active');
            //倒叙
            $('.read-category-chapters').css({ 'flex-direction': 'column' });
        }
        gocate();
    });
    $('.read-category-mask').on('click', function() {

        hidecate();

    });
    $('.js_gologin').on('click', function() {

        _go_url(loginurl);

    });
    //解锁
    $('.js_goPay').on('click', function() {
        $h = $(this).hasClass('lock');
        if ($h) return false;
        $(this).addClass('lock');
        $ob = getnowob();
        $id = $ob.attr('chapter-id');
        // d($id);
        unlock($id, 0);
        hidepay();
        // $(this).removeClass('lock');
    });
    $('.js_godown').on('click', function() {
        _go_url(downurl);
    });

    $('#js_autoBuy').on('click', function() {
        var t = $(this),
            e = t.hasClass("active");
        t[e ? "removeClass" : "addClass"]("active");
        setCookie(autopayindex, !e ? 1 : 0);
    });

    //充值
    $('.js_recharge').on('click', function() {
        _go_url(rechagreurl);
    });
    $('#js_payChapterClose').on('click', function() {
        hidepay();
    });
    $('#js_comment,.js_cancelcomment').on('click', function() {

        hidecomment();
    });
    $('#js_comment .bd').on('click', function() {

        return false;
    });
    $('.js_scomment').on('click', function() {
        subcomment();
        return false;
    });

    $('.chapter-item-wrap').live('click', function() {

        gochapter($(this).attr('chapter-id'));

    });
    //上一页
    $('#js_ftLightBtn').on('click', function() {
        menu();
        if (pre1 == 0) {
            showd(isfirststr);
            return false;
        }
        $url = pgurl + "?id=" + pre1;
        _go_url($url);

    });
    //下一页
    $('#js_ftBookmarkBtn').on('click', function() {
        menu();
        if (next1 == 0) {
            showd(islaststr);
            return false;
        }
        $url = pgurl + "?id=" + next1;
        _go_url($url);
    });
    //评论
    $('#js_ftAutoBtn').on('click', function() {
        menu();
        showcomment();
    });
    $('#js_sharebox').on('click', function() {
        menu();

    });
    //分享
    $('#js_ftSettingBtn').on('click', function() { menu(); });
});

function menu() {
    $('#js_headMenu').toggle();
    $('#js_footMenu').toggle();
}
//定位到上次阅读位置
function gopage() {
    $pg = getQueryVariable('page');
    if (!$pg) return false;
    $ob = $('.acgn-reader-chapter__item-box');
    $pagenum = $ob.children().length;
    $tpagesize = $ob.height();
    //本章单张图片平均尺寸
    $prepagesize = $tpagesize / $pagenum;
    $(".acgn-reader-chapter").scrollTop($pg * $prepagesize);
    $('#js_staticPage').text($pg + '/' + $pagenum);

}
$(window).load(function() {
    gopage();
    initshare('<!--{__ 分享}-->', '', $('#js_sharebox'));
});
//滚动脚本
function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) { return pair[1]; }
    }
    return (false);
}

function loadcate($wait) {
    $data = {
        'bookid': bookid
    };
    var ret;
    yAjax(cateurl, $data, function(data) {
        ret = data.result;
    }, '', !$wait);
    return ret;
}

function showloadbox2() {

    $('.bottom_download').slideUp();
    // $('.mask').slideUp();
    $('.mask').animate({
        top: "0px"
    }, 1000);
}

function islast() {
    //判断是否显示，
    //显示
    $('.islast').show();
    hideload();
}

function haveneedpay() {
    return $('[needpay=1]').length;
}

function waitunlock($chapterid) {
    if (!$chapterid) return false;
    if (!getsecneedpay($chapterid)) return false;
    $isautopay = isautopay();
    if (!$isautopay) return false;
    $data = {
        'bookid': bookid,
        'type': type,
        'sid': $chapterid,
        'autopay': $isautopay
    };

    yAjax(payurl, $data, function(data) {

        if (data.code == 1) {
            changesecstatus($chapterid);
        } else {
            //取消自动解锁
            setCookie(autopayindex, 0);
        }
    }, null, false);
}
//张海靖缓存章节支付状态
function changesecstatus($chapterid) {
    $index = catelistindex($chapterid);
    changecache($index);
}

function catelistindex($chapterid) {
    $cache = getcatechache();
    for (var index = 0; index < $cache.length; index++) {
        if (parseInt($cache[index]['section_id']) == parseInt($chapterid)) {
            return index;
        }
    }
    catecache();
    return index;
}

function getsecneedpay($chapterid) {
    $index = catelistindex($chapterid);
    $data = $cache[$index];
    if ($data['isfree'] == 0) return false;
    if ($data['ispay'] == 1) return false;
    return true;
}
//解锁
function unlock($chapterid, $auto) {
    //解锁回调，目录缓存更新，box needpay状态更新，box内容更新
    $data = {
        'bookid': bookid,
        'type': type,
        'sid': $chapterid,
        'autopay': $auto
    };
    yAjax(payurl, $data, function(data) {

        setTimeout(function() {
            //2秒才能再次点击解锁
            $('.js_goPay').removeClass('lock');
        }, 2000);
        if (data.code == 1) {

            //更新目录缓存
            changesecstatus($chapterid);
            if (!$auto) {
                _go_url(pgurl + '?id=' + $chapterid);
            } else {
                $ob.attr('needpay', 0);
                yAjax('', {
                    'bookid': bookid,
                    'ajax': 1,
                    'id': $chapterid
                }, function(data) {
                    if (data['code'] == 1) {
                        var ret = data['result'];
                        $needpay = parseInt(ret['isfree']) && !parseInt(ret['ispay']) ? 1 : 0;
                        var imgs = '';
                        if (ret['images']) {
                            $.each(ret['images'], function(i, v) {
                                imgs += ' <div style="width:auto" class="acgn-reader-chapter__item loading" chapter-index="1"  dataurl="' + v.url + '"><div class="acgn-reader-chapter__loading-tip">' + i + '</div> </div>'
                            });
                        }
                        $ob.html(imgs);
                        $ob.attr('needpay', 0);
                        fn();
                    }
                });



            }
            //重载页面,自动解锁胡时候不能重载
        } else {
            //弹出错误消息
        }

    }, null, true);
}

function isfirst() { $('.isfirst').show().slideToggle(2000); }

function isload() { $('.isload').show(); }

function hideload() {

    $('.isload').hide();
}

function loading() {

    if (showdload) return false;
    if (lock) return false;
    isload();
    if (secid == 0) {
        //已经是最新一章了
        if (scrollTop + ks_area >= nScrollHight) {
            islast('<!--{__ 已经是最新一章了}-->');
        }
        return false;
    }
    lock = true;
    waitunlock(secid);
    yAjax(pgurl, {
        'bookid': bookid,
        'ajax': 1,
        'id': secid
    }, function(data) {
        //1秒以后才能再次滑动加载
        setTimeout(function() {
            lock = false;
        }, 3000);
        hideload();
        if (data['code'] == 1) {
            var ret = data['result'];
            $needpay = parseInt(ret['isfree']) && !parseInt(ret['ispay']) ? 1 : 0;
            secid = ret['next'];
            next1 = ret['next'];
            pre1 = ret['pre'];
            var imgs = '';
            if (ret['images']) {
                $.each(ret['images'], function(i, v) {
                    imgs += ' <div style="width:auto" class="acgn-reader-chapter__item loading" chapter-index="1" chapter-name="' + ret['title'] + '" dataurl="' + v.url + '"><div class="acgn-reader-chapter__loading-tip">' + i + '</div> </div>'
                });
            }
            var html = "<div chapter-id='" + ret['cart_section_id'] + "' chapter-coin='" + ret['coin'] + "'  class='acgn-reader-chapter__item-box' needpay='" + $needpay + "' id='mid" + ret['list_order'] + "' chapter-name='" + ret['title'] + "'>" + imgs + "</div>";
            $('.acgn-reader-chapter__scroll-box').append(html);
            fn();
            if (isautopay()) {
                // d('自动解锁');
                // unlock(ret['cart_section_id'], 1);
                // setTimeout(() => {
                //     _go_url('#mid' + ret['list_orders']);
                // }, 1000);
            }
        } else {
            showdload = true;
        }
    });
}

function loadpre() {
    if (showdload) return false;
    if (lock) return false;
    if (pre == 0) {
        //已经是最新一章了
        if (scrollTop + ks_area >= nScrollHight) {
            isfirst();
        }
        return false;
    }
    lock = true;
    yAjax('', {
        'bookid': bookid,
        'ajax': 1,
        'id': pre
    }, function(data) {

        //1秒以后才能再次滑动加载
        setTimeout(function() {
            lock = false;
        }, 2000);
        if (data['code'] == 1) {
            var ret = data['result'];
            pre = ret['pre'];
            pre1 = ret['pre'];
            next1 = ret['next'];
            $needpay = parseInt(ret['isfree']) && !parseInt(ret['ispay']) ? 1 : 0;
            var imgs = '';
            if (ret['images']) {
                $.each(ret['images'], function(i, v) {
                    imgs += ' <div style="width:auto" class="acgn-reader-chapter__item loading" chapter-index="1"  dataurl="' + v.url + '"><div class="acgn-reader-chapter__loading-tip">' + i + '</div> </div>'
                });
            }

            var html = "<div chapter-id='" + ret['cart_section_id'] + "' chapter-coin='" + ret['coin'] + "'  class='acgn-reader-chapter__item-box hide' needpay='" + $needpay + "' id='mid" + ret['list_order'] + "' chapter-name='" + ret['title'] + "'>" + imgs + "</div>";
            $id = '#' + $('.acgn-reader-chapter__item-box').eq(0).attr('id');
            $('.acgn-reader-chapter__scroll-box').prepend(html);
            fn();
            setTimeout(function($id) {
                $('.acgn-reader-chapter__item-box.hide').removeClass('hide');
                _go_url($id);
                $(".acgn-reader-chapter").animate({ 'scrollTop': "-=" + 200 + "px" }, 300);
            }, 300, $id);
        } else {
            showdload = true;
        }
    });
}
//获取当前章节DOM对象
function getnowob() {
    $ob = $($('.acgn-reader-chapter__item-box').eq(nows));
    return $ob;
}

function settitle() {
    if (titlelock) return false;
    titlelock = true;
    setTimeout(function() {
        Yscroll();
    }, 200);
}

function Yscroll() {
    $top = $('#reader-scroll').scrollTop();
    if (!lastpointy) {
        lastpointy = $top;
        titlelock = false;
        return 1;
    }
    // if (lastpointy > $top) {
    //     $fx = -1;
    // } else {
    //     $fx = 1;
    // }
    // $cz = Math.abs(lastpointy - $top);

    $all = $('.acgn-reader-chapter__item-box');
    lastpointy = $top;
    $allh = 0, $n = 0, $last = 0;
    //向下滚动
    $.each($all, function(i, v) {
        $n = i;
        $last = $allh;
        $allh += v.offsetHeight;
        if ($top < $allh && $top >= $last) {
            lastrmb = nows;
            nows = $n;
            if (lastrmb != $n) {
                loadpay2();
            }
            //或者滑倒底部
            $h = $allh - ($top + window.innerHeight);
            if ($h < 20) {
                loadpay2();
            }
            // d($n);
            //说明在￥n这个区块
            // d($all.eq($n));
            $ob = $($all.eq($n));
            // d(($n));
            $name = $ob.attr('chapter-name');
            $('#js_headTitle').text($name);
            $('#js_staticChapter').text($name);
            $('title').text($name);
            // window.location.href = changeURLArg(window.location.href, 'id', $ob.attr('chapter-id'))
            //计算页面
            $pg = getpage($ob);
            $('#js_staticPage').text($pg + '/' + $pagenum);
            inittime();
            titlelock = false;
            rmbpoint($ob.attr('chapter-id'), $pg);
            return;
        }
    });

}

function changeURLArg(url, arg, arg_val) {
    var pattern = arg + '=([^&]*)';
    var replaceText = arg + '=' + arg_val;
    if (url.match(pattern)) {
        var tmp = '/(' + arg + '=)([^&]*)/gi';
        tmp = url.replace(eval(tmp), replaceText);
        return tmp;
    } else {
        if (url.match('[\?]')) {
            return url + '&' + replaceText;
        } else {
            return url + '?' + replaceText;
        }
    }
}

function inittime() {
    var myDate = new Date();
    // myDate.getHours();       //获取当前小时数(0-23)
    // myDate.getMinutes();     //获取当前分钟数(0-59)
    $('#js_staticTime').text(myDate.getHours() + ':' + myDate.getMinutes());
}

function getpage($ob) {
    $pagenum = $ob.children().length;
    $tpagesize = $ob.height();
    //本章单张图片平均尺寸
    $prepagesize = $tpagesize / $pagenum;
    $y1 = $ob.offset().top;
    // $y2 = $top;
    $pg = Math.ceil(Math.abs($y1) / $prepagesize);
    if ($pg == 0) {
        $pg = 1;
    }
    return $pg;
}
//阅读记录保存
function rmbpoint($sid, $pg) {
    $index = 'pageindex' + bookid + '_' + type;
    // d($sid + ',' + $pg);
    setCookie($index, $sid + '.' + $pg);
}
$(document).ready(function() {
    //fix底部
    $ob = getnowob(0);
    $ob.attr('needpay', needpay ? 1 : 0);
    inittime();
    loadpay();
    rmbpoint($('.acgn-reader-chapter__item-box').attr('chapter-id'), 1);
    $('.acgn-reader-chapter').scroll(function() {
        scrollTop = $(this).scrollTop();
        ks_area = $(this).innerHeight();
        //滚动距离总长(注意不是滚动条的长度)  
        nScrollHight = $('body')[0].scrollHeight;
        $h = $('.acgn-reader-chapter__scroll-box').innerHeight();
        settitle();
        if (!haveneedpay()) {
            if (scrollTop > ($h - nScrollHight * 1.5)) {
                loading();
            }
            if ($(this).scrollTop() == 0) {
                loadpre();
            }
        }
    });

});