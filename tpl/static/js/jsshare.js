var typeall = ['facebook', 'twitter', 'google', 'wechat', 'sina', 'qq', 'qzone', 'baidu', 'douban', 'pinterest', 'renren', 'copy'];
var sharebox = '';
var shareclickin = '';
//js分享
function share(type, title, pic, url) {
    switch (type) {
        case 'sina':
            $baseurl = "http://v.t.sina.com.cn/share/share.php?";
            $title = "&title=" + encodeURIComponent(title);
            $pic = "&pic=" + encodeURIComponent(pic);
            $url = "&sourceUrl=" + encodeURIComponent(url);
            $key = "&appkey=" + '895033136';
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'baidu':
            $baseurl = "http://tieba.baidu.com/f/commit/share/openShareApi?";
            $title = "&title=" + encodeURIComponent(title);
            $pic = "&pic=" + encodeURIComponent(pic);
            $url = "&url=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'qq':
            $baseurl = "https://connect.qq.com/widget/shareqq/index.html?";
            $title = "&title=" + encodeURIComponent(title);
            $pic = "&pics=" + encodeURIComponent(pic);
            $url = "&sourceUrl=" + encodeURIComponent(url);
            $key = "&appkey=" + '5bd32d6f1dff4725ba40338b233ff155';
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'douban':
            $baseurl = "http://shuo.douban.com/!service/share?";
            $title = "&name=" + encodeURIComponent(title);
            $pic = "&image=" + encodeURIComponent(pic);
            $url = "&href=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'qzone':
            // $baseurl = "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?";
            // $title = "&title=" + encodeURIComponent(title);
            // $pic = "&image=" + encodeURIComponent(pic);
            // $url = "&url=" + encodeURIComponent(url);
            // $key = "";
            $baseurl = "https://h5.qzone.qq.com/q/qzs/open/connect/widget/mobile/qzshare/index.html?page=qzshare.html&loginpage=loginindex.html&logintype=qzone&referer=https://m.taomanhua.com/";
            $title = "&title=" + encodeURIComponent(title);
            $pic = "&imageUrl=" + encodeURIComponent(pic);
            $url = "&url=" + encodeURIComponent(url);
            $desc = "&desc=" + encodeURIComponent(title);
            $summary = "&summary=" + encodeURIComponent(title);
            $site = "&site=" + encodeURIComponent(title);
            $referer = "&referer=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $desc+$site+$summary+$referer;
            _go_url($shareurl);

            break;
        case 'renren':
            $baseurl = "http://share.renren.com/share/buttonshare.do?";
            $title = "&title=" + encodeURIComponent(title);
            $pic = "&image=" + encodeURIComponent(pic);
            $url = "&link=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'kaixin':
            $baseurl = "http://www.kaixin001.com/repaste/share.php?";
            $title = "&rtitle=" + encodeURIComponent(title);
            $pic = "&image=" + encodeURIComponent(pic);
            $url = "&link=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'facebook':
            $baseurl = "http://www.facebook.com/sharer/sharer.php?";
            $title = "&t=" + encodeURIComponent(title);
            $pic = "&image=" + encodeURIComponent(pic);
            $url = "&u=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'twitter':
            $baseurl = "http://twitter.com/intent/tweet?";
            $title = "&text=" + encodeURIComponent(title);
            $pic = "&image=" + encodeURIComponent(pic);
            $url = "&url=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;

        case 'google':
            $baseurl = "https://plus.google.com/share?";
            $title = "&t=" + encodeURIComponent(title);
            $pic = "&image=" + encodeURIComponent(pic);
            $url = "&url=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);

            break;
        case 'pinterest':
            $baseurl = "https://www.pinterest.com/pin/create/button/?";
            $title = "&description=" + encodeURIComponent(title);
            $pic = "&media=" + encodeURIComponent(pic);
            $url = "&url=" + encodeURIComponent(url);
            $key = "";
            $shareurl = $baseurl + $title + $pic + $url + $key;
            _go_url($shareurl);
            break;
        default:













            // 开心网


            break;
    }
}
/**
 * 初始话分享
 * @param {string} title  分享弹出的文字，传对应语言文本
 * @param {array} types 传对应，就只显示对应个数['sina', 'qq', 'qzone', 'baidu', 'wexin', 'douban', 'kaixin', 'twitter', 'pinterest', 'google', 'renren'];
 * @param {string} devietype 设备类型 wap手机端 或者web pc端
 * @param {domobject}} $obj  绑定分享按钮 ,传入按钮可以绑定属性title，url ，pic 
 */
function initshare(title, types, $obj) {
    $html = '<div class="bd js_sharebox acgn-clearfix"><div class="hd"><span class="title">' + title + '</span><i class="icon icon-comm-close close js_chare_close"></i></div><ul class="acgn-share"></ul></div>';
    $btn = '<li class="item shareicon "><i class=""></i></li>';
    sharebox = $($html);
    shareclickin=$obj;
    var devietype = 'wap';
    if ((navigator.userAgent.match(/(iPhone|iPod|Android|ios|iOS|iPad|Backerry|WebOS|Symbian|Windows Phone|Phone)/i))) {
        devietype = 'wap';
    } else {
        devietype = 'web';
    }
    switch (devietype) {

        case 'wap':
            if (!types) {
                types = typeall;
            }

            $ul = sharebox.find('.acgn-share');
            sharebox.addClass(devietype);
            $.each(types, function (i, v) {
                $tmp = $($btn);
                $tmp.addClass('icoift-' + v + '-bg');
                $tmp.attr('date-type', v);
                $tmp.children('i').addClass('icoift-' + v);
                $ul.append($tmp);
            });
            $('body').append(sharebox);
            $obj.bind('click', function () {
                shareclickin = $(this);
                sharebox.slideToggle();
                // $('.js_sharebox').show();
                // d(sharebox);
            });
            break;

        case 'web':
            if (!types) {
                types = typeall;
            }

            $ul = sharebox.find('.acgn-share');
            sharebox.addClass(devietype);
            sharebox.addClass('move');
            $.each(types, function (i, v) {
                $tmp = $($btn);
                $tmp.addClass('icoift-' + v + '-bg'); $tmp.attr('date-type', v);
                $tmp.children('i').addClass('icoift-' + v);
                $ul.append($tmp);
            });
            $('body').append(sharebox);
            $obj.bind('click', function () {
                sharebox.fadeToggle();
                // $('.js_sharebox').show();
                // d(sharebox);
            });
            break;

    }
}
$(function () {
    $('.shareicon').live('click', function () {

        $type = $(this).attr('date-type');
        sharebox.hide();
        $title = shareclickin.attr('title') ? shareclickin.attr('title') : $('title').text();
        $url = shareclickin.attr('url') ? shareclickin.attr('url') : location.href;
        $pic = shareclickin.attr('pic') ? shareclickin.attr('pic') : $('img').eq(0).attr('src');

        share($type, $title, $pic, $url);
    });
    $('.js_chare_close').live('click', function () {
        sharebox.hide();
    });
});

