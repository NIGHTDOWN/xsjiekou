/**
 *@author :by oe.night
 *@time:2015/03/02
 *ajax（单，多）文件上传、
 */
/*----------------------------------------可以修改的变量----------------------------------------*/
//调试接口
var DEBUG = false;
var D_DEBUG = false;
var MAX_YS_SIZE = 1024; //上传图片大小到多少时开启压缩kb
var MAX_YS_WIDTH = 1000; //图片的最大压缩宽度
var YS_QUALITY = 0.7; //图片的压缩质量
var yslook = 0;
//进度条图标的id
var _loadobj_id = 'ajaxload_view';
/*----------------------------------------需要页面初始化的变量----------------------------------------*/
//ajax同步
var _ajax_asyn = true;
//ajax后台url
var _admin_ajax_url = null;
//ajax文件上传url,页面初始化
var _ajax_file_url = null;
var _ajax_area_url = null;
var _ajax_edit_url = null;
/*----------------------------------------全局变量----------------------------------------*/
var fileobj;
var boxobj;
/*----------------------------------------全局私有变量----------------------------------------*/
//ajax请求的对象
var prevobj = null;
var prevobjval = null;
var sbumitloop = 0;
//from状态检测标识/null
var _from_statu = false;
//进度条对象
var _loadobj;
var _img_view = function() {};
//图片上传回调函数
var _img_process = function() {};
var _img_cut_process = function() {};
// 页面检测函数,需要改变_from_statu的值
var checkpage = function() {};
var _file_process = _file_recv;
// 区域对象
var _area_obj = null;
var boxynobj = null;
/*----------------------------------------与html有关的变量----------------------------------------*/
//进度条html
var __loadobj_html = '<div id="' + _loadobj_id + '" style="position:fixed;width:100%;height:100%;top:0px;z-index:9999;cursor:move;"><img src="/tpl/static/images/load.gif" style="left:50%;position:relative;top:50%;text-algin:center"/></div>';
var __cut_img_html = '<div id="cut_img"><div class="cut_top"><div class="cut_left" style="float:left"><img class="cut_before_view" width="400px" src=""/></div><div class="cut_right" style="float:left"><div class="cut_right_top" style="overflow: hidden;margin:0px 20px 20px 20px"><img src="" class="cut_after_view"/></div><div class="cut_right_buttom"><div></div></div></div></div><div class="cut_button"><input class="cut_sure" value="确定" type="button"/></div></div>';
//确认操作框

var __boxyn_html = '<div class="oem_alert" >确定？<br /><div class="oem_alert_btt"><input type="button" class="alert_1" value="确定"  onmouseout="$(this).attr(\'class\',\'alert_1\')" style="margin-left: 15px;padding: 8px;background: #fc5f00;color: rgb(10, 10, 10);border-radius: 8px;" onmousemove="$(this).attr(\'class\',\'alert_1s\')" onclick="boxyndo(1)" /><input type="button" class="alert_2" value="取消" style="margin: 15px;padding: 8px;background: #fc5f00;color: rgb(10, 10, 10);border-radius: 8px;" onclick="boxyndo(0)" onmouseout="$(this).attr(\'class\',\'alert_2\')" onmousemove="$(this).attr(\'class\',\'alert_2s\')"/></div></div>';
var __area_box_head = {
    'l1': '省/直辖市',
    'l2': '城市',
    'l3': '县区',
    'l4': '街道'
};
var area_level = null;
var ws;

/*----------------------------------------内部函数,类----------------------------------------*/
//ImgD:要放图片的img元素，onload时传参可用this
//h:img元素的高度，像素
//w:img元素的宽度，像素
function _deal_dom_img(ImgD, h, w) {
    var getContainer = ImgD;
    var getIMG = $(getContainer).find('img').get(0);
    $jqimg_p = $(getContainer);
    $jqimg = $(getIMG);

    $("<img/>").attr('src', $(getIMG).attr('src')).load(function() {

        $sh = this.height;
        $sw = this.width;
        //d($jqimg_p);
        //d($jqimg);
        $m = $sw / w;
        $n = $sh / h;
        if ($m < $n) {
            $w = Math.ceil($sw / $n);
            $h = Math.ceil($sh / $n);
        } else {
            $w = Math.ceil($sw / $m);
            $h = Math.ceil($sh / $m);
        }
        $jqimg.height($h);
        $jqimg.width($w);
        //居中
        $left = ($jqimg_p.width() - $w) / 2;
        $top = ($jqimg_p.height() - $h) / 2;
        $jqimg.css({ 'margin-top': $top, 'margin-left': $left });

    });
}

function img_auto_size(ImgD, h, w) {
    ImgD.each(function(dom) {

        _deal_dom_img(ImgD.get(dom), h, w);
    });


}
var cdebug = d;

function _click($obj) {

    $obj.click();
    // if(!+[1,]){
    // /// cdebug('sfs'+$obj);
    // $obj.click();
    // }
    // else{
    // //cdebug($obj);
    // var evt = document.createEvent("MouseEvents");
    // evt.initEvent("click", true, true);
    // cdebug($obj.get(0).dispatchEvent(evt));
    // }
}
//要删除节点,节点父下有多少节点判断要不要删除节点;筛选子节点
function delobj(obj, $start_check, $class) {
    if ($start_check) {
        $leng = obj.parent().children('.' + $class + '').length;
        if ($leng > 1) {
            obj.remove();

        } else {
            showd('至少保留一项');
        }
    } else {
        obj.remove();
    }
    // obj.remove();

}
//显示进度条

function _load_view_show() {
    if (DEBUG) {
        _loadobj = $('');
        return;
    }
    _loadobj = $('#' + _loadobj_id);
    if (_loadobj.html() == undefined) {
        $e = $(__loadobj_html);
        $('body').append($e);
        _loadobj = $('#' + _loadobj_id);
    } else {
        _loadobj.remove();
        arguments.callee();
    }
}

function _file_recv() {}
//隐藏进度条
function _load_view_hide() {
    try {
        _loadobj.remove();
    } catch (e) {}

}
/*文件上传对象
参数url、回调、上传类型、数据类型、
函数单文件上传、多文件上传
send、sendmore*/

function _upfile() {
    this.url = null;
    this._call_back = null;
    this.type = 'json';
    this.fileobjid = null;
    this.method = 'post';
    this.beforeSend = _load_view_show;
    this.complete = _load_view_hide;
    this.async = false;
    this.load_show = true;
    this.obj = null;
    this.send = function() {
        if (this.load_show) {
            this.beforeSend.call(this);
        }
        //必须有name的值

        $name = $('#' + this.fileobjid).attr('name');
        if ($name == '' || $name == undefined) {
            $('#' + this.fileobjid).attr('name', 'tmp_' + this.fileobjid);
        }
        $name = (new Date().getTime()) + "_" + parseInt(Math.random() * 100000, 10);

        $.ajaxFileUpload({
            'url': this.url,
            'secureuri': false,
            'fileElementId': this.fileobjid,
            'dataType': 'jsonp',
            'data': {
                id: this.fileobjid,
                'ajax': 'ajax',
                'name': $name
            },
            async: this.async,
            beforeSend: function(xmlobj) {

                // cdebug('start');
            },
            complete: function($data) {

                if (_loadobj.html() != undefined) {
                    fileobj.complete.call(this);
                }
            },
            success: function($data) {

                if ($data) {

                    _ajax_recv($data, fileobj._call_back);
                    return 1;
                }


            },
            error: function($data, e, b) {
                $domain = window.location.host;
                $checkurl = $domain + '/index.php?m=admin&c=aysnqian&a=imgisup&name=' + $name;
                /* yAjax($checkurl,null,_ajax_recv($data, fileobj._call_back));      */

                $.get($checkurl, function($data) {
                    try {

                        $data = jta($data);
                        if ($data.flag) {

                            s = $data.data.split(",");
                            $name = upimgid;
                            $id = 'viewimg_' + $name;
                            imgobj = $('#' + $id);

                            $img = '';
                            $.each(s, function($key, $value) {

                                $style = " background: none repeat scroll 0 0 #fafafa; border: 1px solid #ebebeb;border-radius: 3px; cursor: pointer;display: block;margin-top: 10px;width: 80px;";
                                $img += '<div style="float:;width:auto;"><div class="upimg_view" style="height:80px;width:80px;overflow:hidden;cursor:pointer;margin-top:5px;" ><img alt="点击查看大图" src="' + $value + '" width="80px" onclick="window.open(\'' + $value + '\');"/> </div><input type="hidden" value="' + $value + '" name="' + $name + '"/> <button type="button" style="' + $style + '" onclick="javascript:delobj($(this).parent(\'div\'));">删除</button></div>';
                            });

                            if (imgobj.html() == undefined) {
                                var imgbox = '<dl class="lineD" id="' + $id + '"><dd>' + $img + '</dd><div style="clear:both"></div></dl>';
                                obj.after(imgbox);
                            } else {
                                imgobj.html($img);

                            }
                            img_auto_size(imgobj.find('.upimg_view'), '80', '80');
                        }
                    } catch (e) {
                        d(e);
                    }
                });



                /*  d('文件上传错误' + $data.error);*/
                //   _ajax_recv(data, fileobj._call_back);

            }
        })
        return false;
    }
    this.sendmore = function() {
        try {
            data = new FormData();
        } catch (e) {
            alert('请使用升级您的浏览器');
        }
        if (this.load_show) {
            this.beforeSend.call(this);
        }
        obj = $('#' + this.fileobjid);
        $.each(obj[0].files, function(i, file) {
            data.append('upload_file' + i, file);
        });
        $.ajax({
            'url': this.url,
            'type': this.method,
            'data': data,
            'cache': false,
            'dataType': this.type,
            'contentType': false,
            'processData': false,
            async: this.async,
            beforeSend: function() {},
            complete: function() {
                if (_loadobj.html() != undefined) {
                    fileobj.complete.call(this);
                }
            },
            success: function(data) {

                _ajax_recv(data, fileobj._call_back);
            },
            error: function(data, status, e) {
                d('文件上传错误' + data);
            }
        });
    };
};

//裁剪图片
//option包含上传的地址，裁剪宽高，回调
function _cut_img($imgurl, $option) {
    //cdebug($(__cut_img_html).html());
    if (_tobool($option)) {
        $option = _strtoobj($option);
    }
    msgbox('裁剪', $(__cut_img_html), null, null, null, null, 666);
    boxobj.hide();
    boxobj.find('img').attr('src', $imgurl);
    // $('input.cutimg_img').val($imgurl);
    //裁剪宽高
    xsize = $option.width ? $option.width : '50',
        ysize = $option.height ? $option.width : '50';
    $pz = xsize / ysize;
    // cdebug($(boxobj).find('img.cut_before_view'));
    $cutdoobj = $(boxobj).find('img.cut_before_view');
    $cutview = $(boxobj).find('img.cut_after_view');
    $s = xsize / 50;
    $cutview.parent().width(50);
    $cutview.parent().height(ysize / $s);
    $cutdoobj.Jcrop({
        onChange: showPreview,
        onSelect: showPreview,
        aspectRatio: $pz,
        setSelect: [
            0,
            0,
            xsize,
            ysize
        ],
        boxWidth: 400,
        boxHeight: 300
    });
    boxobj.show();
    //简单的事件处理程序，响应自onChange,onSelect事件，按照上面的Jcrop调用
    var cut_x,
        cut_y,
        cut_w,
        cut_h;
    var reta_width,
        reta_height;

    function showPreview(coords) {
        if (parseInt(coords.w) > 0) {
            //计算预览区域图片缩放的比例，通过计算显示区域的宽度(与高度)与剪裁的宽度(与高度)之比得到
            var rx = $cutview.parent().width() / coords.w;
            var ry = $cutview.parent().height() / coords.h;
            //通过比例值控制图片的样式与显示
            // cdebug($cutview);
            //cdebug(ry);
            $cutview.css({
                width: Math.round(rx * $cutdoobj.width()) + 'px', //预览图片宽度为计算比例值与原图片宽度的乘积
                height: Math.round(rx * $cutdoobj.height()) + 'px', //预览图片高度为计算比例值与原图片高度的乘积
                marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                marginTop: '-' + Math.round(ry * coords.y) + 'px'
            });
            $pic_width = $cutdoobj.width();
            $pic_height = $cutdoobj.height();
            $('<img/>').attr('src', $cutdoobj.attr('src')).load(function() {
                $pic_real_width = this.width;
                $pic_real_height = this.height;
            });
            reta_width = $pic_real_width / $pic_width;
            reta_height = $pic_real_height / $pic_height;
            cut_x = (coords.x) * reta_width;
            cut_y = (coords.y) * reta_height;
            cut_w = (coords.w) * reta_width;
            cut_h = (coords.h) * reta_height;
        }
    }
    //确认剪切

    boxobj.find('.cut_sure').live('click', function() {
        // cdebug('sdfsdf');
        // cdebug(parseInt(cut_w)&&parseInt(cut_h));
        if (parseInt(cut_w) && parseInt(cut_h)) {
            var act = $('#act').val();
            var pic_name = $('#cut_after_view').val();
            // var x = $("#x").val();
            // var y = $("#y").val();
            // var w = $("#w").val();
            // var h = $("#h").val();
            // cdebug('dfs');
            $.post($option.url, {
                pic_name: pic_name,
                x: cut_x,
                y: cut_y,
                w: cut_w,
                h: cut_h
            }, function(data) {
                if (data.status == 1) {
                    var _call_back = $option.callback;
                    //var win = art.dialog.open.origin;
                    if (_call_back && typeof(_call_back) == 'function') {
                        try {
                            win[_call_back].call(this, data.file);
                            closebox();
                        } catch (e) {
                            alert('确认异常');
                        };
                    }
                } else {
                    alert(data.error);
                    return false;
                }
            }, 'json');
            return false;
        }
        return false;
    });
}
//压缩图片
function __ys_class() {
    /*
    三个参数
    file：一个是文件(类型是图片格式)，
    w：一个是文件压缩的后宽度，宽度越小，字节越小
    callback：一个是容器或者回调函数
    photoCompress()
    */

    this.callback = null;
    var _ngys = this;
    this.photoCompress = function(file, w, callback) {
        var ready = new FileReader();
        /*开始读取指定的Blob对象或File对象中的内容. 当读取操作完成时,readyState属性的值会成为DONE,如果设置了onloadend事件处理程序,则调用之.同时,result属性中将包含一个data: URL格式的字符串以表示所读取文件的内容.*/
        ready.readAsDataURL(file);
        ready.onload = function() {
            var re = this.result;

            _ngys.canvasDataURL(re, w, callback);
        }
    }

    this.canvasDataURL = function(path, obj, callback) {
            this.callback = callback;
            var img = new Image();
            img.src = path;

            img.onload = function() {
                var that = this;
                // 默认按比例压缩
                var w = that.width,
                    h = that.height,
                    scale = w / h;

                w = w > MAX_YS_WIDTH ? MAX_YS_WIDTH : w; //宽度最大1000
                w = obj.width || w;
                h = obj.height || (w / scale);
                var quality = YS_QUALITY; // 默认图片质量为0.7
                //生成canvas
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                // 创建属性节点
                var anw = document.createAttribute("width");
                anw.nodeValue = w;
                var anh = document.createAttribute("height");
                anh.nodeValue = h;
                canvas.setAttributeNode(anw);
                canvas.setAttributeNode(anh);
                ctx.drawImage(that, 0, 0, w, h);
                // 图像质量
                if (obj.quality && obj.quality <= 1 && obj.quality > 0) {
                    quality = obj.quality;
                }
                // quality值越小，所绘制出的图像越模糊
                var base64 = canvas.toDataURL('image/jpeg', quality);
                // 回调函数返回base64的值
                /*  callback(base64);*/

                _ngys.convertBase64UrlToBlob(base64);
            }
        }
        /**
         * 将以base64的图片url数据转换为Blob
         * @param urlData
         *            用url方式表示的base64图片数据
         */
    this.convertBase64UrlToBlob = function(urlData) {

        var arr = urlData.split(','),
            mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]),
            n = bstr.length,
            u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        var ngboob = new Blob([u8arr], { type: mime });
        this.callback(ngboob);
    }
}
//获取图片大小,返回kb
function getimgsize($obj) {
    if ($.isEmptyObject($obj)) return false;
    $fileobj = $obj.get(0).files[0];
    $size = $fileobj.size;
    return $size / 1024;
}
//$objid文件上传对象id
//压缩完成直接上传
function _ys_img_sc($objid, $url, $callback) {
    $jqobj = $('#' + $objid);

    if ($.isEmptyObject($jqobj)) return false;

    $fileobj = $jqobj.get(0).files[0];
    $size = $fileobj.size;


    if ($size / 1024 > MAX_YS_SIZE) {
        //压缩
        $ysob = new __ys_class();
        $ysfile = $ysob.photoCompress($fileobj, {

        }, function(data) {

            /*$file2=new  File([data],'ngysfile'+ Date.parse(new Date())+".jpg");*/
            //这里有阻塞
            //执行完压缩在上传好了
            var form = new FormData();
            form.append("file", data, "ngysfile_" + Date.parse(new Date()) + ".jpg"); // 文件对象
            form.append("ajax", "ajax");

            xhr = new XMLHttpRequest(); // XMLHttpRequest 对象
            xhr.open("post", $url, true); //post方式，url为服务器请求地址，true 该参数规定请求是否异步处理。
            xhr.onload = function($data) {
                var $data = JSON.parse($data.target.responseText);
                if (typeof($callback) == 'string') {
                    $callback = eval($callback);
                    if (typeof($callback) == 'function') {



                        $callback.call(this, $data);
                    }
                    /*$callback.call(this,data);*/
                } else {

                    $callback.call(this, $data);
                }

            }; //请求完成
            xhr.onerror = function($data) {
                var $data = JSON.parse($data.target.responseText);
                if (typeof($callback) == 'string') {
                    $callback = eval($callback);
                    if (typeof($callback) == 'function') {



                        $callback.call(this, $data);
                    }
                    /*$callback.call(this,data);*/
                } else {

                    $callback.call(this, $data);
                }
            }; //请求失败

            xhr.upload.onprogress = function(evt) {
                d(evt);
                d('上传进度' + evt);
                return 1;
                var progressBar = document.getElementById("progressBar");
                var percentageDiv = document.getElementById("percentage");
                // event.total是需要传输的总字节，event.loaded是已经传输的字节。如果event.lengthComputable不为真，则event.total等于0
                if (evt.lengthComputable) {
                    progressBar.max = evt.total;
                    progressBar.value = evt.loaded;
                    percentageDiv.innerHTML = Math.round(evt.loaded / evt.total * 100) + "%";
                }
                var time = document.getElementById("time");
                var nt = new Date().getTime(); //获取当前时间
                var pertime = (nt - ot) / 1000; //计算出上次调用该方法时到现在的时间差，单位为s
                ot = new Date().getTime(); //重新赋值时间，用于下次计算
                var perload = evt.loaded - oloaded; //计算该分段上传的文件大小，单位b
                oloaded = evt.loaded; //重新赋值已上传文件大小，用以下次计算
                //上传速度计算
                var speed = perload / pertime; //单位b/s
                var bspeed = speed;
                var units = 'b/s'; //单位名称
                if (speed / 1024 > 1) {
                    speed = speed / 1024;
                    units = 'k/s';
                }
                if (speed / 1024 > 1) {
                    speed = speed / 1024;
                    units = 'M/s';
                }
                speed = speed.toFixed(1);
                //剩余时间
                var resttime = ((evt.total - evt.loaded) / bspeed).toFixed(1);
                time.innerHTML = '，速度：' + speed + units + '，剩余时间：' + resttime + 's';
                if (bspeed == 0) time.innerHTML = '上传已取消';


            }; //【上传进度调用方法实现】
            xhr.upload.onloadstart = function() { //上传开始执行方法
                ot = new Date().getTime(); //设置上传开始时间
                oloaded = 0; //设置上传开始时，以上传的文件大小为0
            };

            xhr.send(form); //开始上传，发送form数据



            return true;
            /*$fileobj=$file2;*/

        });

        return true;
    }

    return false;
}

function _form_error($msg) {
    $msg = '<span class="oe_trap_error"><label></label><em>' + $msg + '</em></span>';
    return $msg;
}

function _form_ok($msg) {

    $msg = '<span class="oe_trap_ok"><label></label><em>' + $msg + '</em></span>';
    return $msg;
}

function _form_msg($msg) {
    $msg = '<span class="oe_trap"><label></label><em>' + $msg + '</em></span>';
    return $msg;
}
//jq扩展提示框



/**---------------------分割线--------------------------**/
function change_form_statu($bool) {

    $bool = _tobool($bool);
    if (_from_statu === 0) {

        _from_statu = $bool;

    } else {
        _from_statu = _from_statu && $bool;
    }

}

function jta(str) {
    $myObject = eval('(' + str + ')');
    if (typeof($myObject) == 'object') {
        return $myObject;
    } else {
        return false;
    }

}

function atj(obj) {
    return JSON.stringify(obj);
}

function _ajax_recv($json_obj, $fun) {

    if (typeof($json_obj) == 'object') {
        if ($json_obj.flag) {
            //d(typeof ($fun));
            try {
                if (typeof($fun) == 'string') {

                    $fun = eval($fun);
                }

            } catch (e) {}
            if (typeof($fun) == 'function') {

                $fun.call(this, $json_obj);
            }
            //  _go_url($json_obj.url);
        } else {
            showd($json_obj.error.errormsg);
        }
        if (_tobool($json_obj.url)) {
            _go_url($json_obj.url);
        }

    } else {
        try {
            if (jta($json_obj)) {

                arguments.callee(jta($json_obj));
            } else {

                showd($json_obj);
                return;
            }
        } catch (e) {

        }


    }

    return;
}

function _jqformdeal($data) {

    if (typeof($data) == 'string') {
        $data = jta($data);
        if (typeof($data) == 'string') {
            adebug($data);
        }
    };
    //$r=jta($data);
    //把产品id添加到当前的form
    //if($r.productid)
    try {
        if (data.actid != undefined) {
            $('[name=actid]').val($data.actid);
        }
        if (data.productid != undefined) {
            $('[name=productid]').val($data.productid);
        }
    } catch (e) {}
    // adebug($data.productid);

    if ($data.errorid != null) {
        $msg = errcode[$data.errorid];

        adebug($msg);
        if ($data.errorid == 5 || $data.errorid == 6) {
            closebox();
        }

    }
    if ($data.msg != null) {

        adebug($data.msg);
    }
}
//表单无刷新核心
//将form转为AJAX提交
function ajaxSubmit(frm, fun) {
    _load_view_show();

    var dataPara = getFormJson(frm);
    $.ajax({
        url: frm.attr('action'),
        type: frm.attr('method'),
        dataType: 'json',
        data: dataPara,
        success: function(dataPara) {

            _load_view_hide();

            _ajax_recv(dataPara, fun);

        }

    });
}

//将form中的值转换为键值对。
function getFormJson(frm) {
    var o = {};
    var a = frm.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });

    return o;
}

function _img_cut_recv($data, $option, $callback) {
    //剪图
    _cut_img($data.data.source, $option, $callback);
    //传给php
    //
}

function _strtoobj(str) {
    return eval('({' + str + '})');
}

function _find_parent($obj, $search) {

    $obj = $obj.parentsUntil($search);



    return $($obj.get($obj.length - 1)).parent($search);
}

function _objtostr(o) {
    var r = [];
    if (typeof o == 'string' || o == null) {
        return o;
    }
    if (typeof o == 'object') {
        if (!o.sort) {
            r[0] = '{'
            for (var i in o) {
                r[r.length] = i;
                r[r.length] = ':';
                r[r.length] = _objtostr(o[i]);
                r[r.length] = ',';
            }
            r[r.length - 1] = '}'
        } else {
            r[0] = '['
            for (var i = 0; i < o.length; i++) {
                r[r.length] = _objtostr(o[i]);
                r[r.length] = ',';
            }
            r[r.length - 1] = ']'
        }
        return r.join('');
    }
    return o.toString();
}

function _tobool($data) {
    if ($data == 'undefined' || $data == 'null' || $data == 'false' || $data == 'NaN') {
        return false;
    } else {
        return Boolean($data);
    }
}

function _area_select_click($id, $text, $jqobj) {
    //选中该样式，改变文本，改变隐藏的id
    _area_obj = _find_parent($jqobj, '.oe_diqu');
    $obj = _area_obj;
    $class = 'current';
    $obj.find('#oe_diqu_choose').val($id);
    $jqobj.parent('.current').children('span').removeClass($class);
    $jqobj.addClass($class);
    $index = _area_obj.find('.oe_diqu_content h6 a').index($('.oe_diqu_content h6 .current'));
    $level = $index + 1;
    if ($level == area_level || $level >= 4) {
        change_statu($obj.find(".oe_diqu_content"));
    }
    if ($obj.find('.oe_diqu_con b').index() < 0) {
        $obj.find('.oe_diqu_con').html('');
    }
    $areahide = '<input type="hidden" name="area' + $level + '" value="' + $id + '">';
    if ($obj.find('.oe_diqu_con').find('.oe_area_text_' + $index).index() < 0) {
        $html = '<b class=\'oe_area_text_' + $index + '\'>' + $areahide + ' ' + $text + '</b>';
        $obj.find('.oe_diqu_con').html($obj.find('.oe_diqu_con').html() + $html);
    } else {
        $it = $obj.find('.oe_diqu_con').find('.oe_area_text_' + $index);
        $it.html($areahide + ' ' + $text);
        $it.nextAll('b').remove();
    }
    //ajax请求子地区，显示之类
    //ajax点击时检测现在的_area_obj
    $obj = ajax_get_area($id);
}

function _load_area_child_hrml($area_arr) {
    $html = '';
    if (typeof($area_arr) == 'object') {
        $.each($area_arr, function($i, $v) {
            if (_tobool($v.id) && _tobool($v.areaname)) {
                $html = $html + '<span onclick="_area_select_click(\'' + $v.id + '\',\'' + $v.areaname + '\',$(this))">' + $v.areaname + '</span>';
            }
        });
    }
    return $html;
}

function _go_url($url) {
    this.location.href = $url;
}

function _go_url_new($url) {

    window.open($url);
}

function encode(s) {
    return s.replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/([\\\.\*\[\]\(\)\$\^])/g, '\\$1');
}

function decode(s) {
    return s.replace(/\\([\\\.\*\[\]\(\)\$\^])/g, '$1').replace(/>/g, '>').replace(/</g, '<').replace(/&/g, '&');
}

function _load_tab($jqobj_parent, $jqobj_child, $change_class, $this_obj) {
    _area_obj = _find_parent($($this_obj), '.oe_diqu');
    $index = $jqobj_parent.index($this_obj);
    if ($index < 0) {
        return 0;
    }
    $($jqobj_parent).removeClass($change_class);
    $($this_obj).addClass($change_class);
    $jqobj_child.css('display', 'none');
    $($jqobj_child.get($index)).css('display', '');
}
var __sto = setTimeout;
window.setTimeout = function(callback, timeout, param) {
    try {


        var args = Array.prototype.slice.call(arguments, 2);
        var _cb = function() {
            callback.apply(null, args);
        }
        __sto(_cb, timeout);
    } catch (e) {

    }
}
var i = 60;
//按钮禁用倒计时
function countdown(obj, msg) {
    if (obj != undefined && i != 0) {
        $s = '秒后重新获取';
        $m = i + $s;
        i--;
        obj.val($m);
        $t = setTimeout(countdown, 1000, obj, msg);
        obj.attr('disabled', 'ture');
    } else {
        i = 60;
        clearTimeout($t);
        obj.val(msg);
        obj.attr('disabled', false);

    }



}


function loopSearch(s, obj) {
    var cnt = 0;
    if (obj.nodeType == 3) {
        cnt = replace(s, obj);
        return cnt;
    }
    for (var i = 0, c; c = obj.childNodes[i]; i++) {
        if (!c.className || c.className != 'oe_search_word')
            cnt += loopSearch(s, c);
    }
    return cnt;
}

function replace(s, dest) {
    var r = new RegExp(s, 'g');
    var tm = null;
    var t = dest.nodeValue;
    var cnt = 0;
    if (tm = t.match(r)) {
        cnt = tm.length;
        t = t.replace(r, '{searchHL}' + decode(s) + '{/searchHL}')
        dest.nodeValue = t;
    }
    return cnt;
}

function cgstatus(data) {
    //cdebug(prevobj);
    if (data == 1) {
        //cdebug(prevobj.val());
        $value = prevobj.children('div').attr('class');
        switch ($value) {
            case 'yes':
                prevobj.children('div').attr('class', 'no');
                break;
            case 'no':
                prevobj.children('div').attr('class', 'yes');
                break;

        }
    } else {
        //prevobj.parent().html(prevobjval);
    }
}

function trim(str, findstr) {
    $word = findstr;
    if ($word == undefined) {
        $word = '\\s*';
    } else {
        $word = ($word);
    }
    $find = new RegExp("(^" + $word + ")|(" + $word + "$)", "g");

    return str.replace($find, "");
}

function cgdivflag(data) {

    if (data.flag) {
        $value = prevobj.children('div').attr('class');
        switch ($value) {
            case 'yes':
                prevobj.children('div').attr('class', 'no');
                break;
            case 'no':
                prevobj.children('div').attr('class', 'yes');
                break;

        }
    } else {
        showd(data.error.errormsg);
    }
}

function addfavorite($obj) {

    var link = $obj.attr('url');

    var title = window.document.title;
    if (document.all) {

        window.external.addFavorite(link, title);
    } else if (window.sidebar) {

        window.sidebar.addPanel(title, link, "");
    }


}

function fastajax(obj, val, fun) {
    $now = $(obj);
    $text = $now.val();
    if (val != undefined) {
        $text = val
    }

    if ($text != prevobjval || fun == cgdivflag) {

        $name = $now.attr('name');
        $key = $now.attr('key');
        $url = _admin_ajax_url;
        $who = $now.attr('who');
        $v = {
            'key': $key,
            'name': $name,
            'value': $text
        };
        if ($who != '') {
            $v = {
                'key': $key,
                'name': $name,
                'value': $text,
                'who': $who
            };
        }
        $val = makePost($v);
        if (fun == undefined) {
            fun = _ajax_recv;
        }
        yAjax($url, $val, fun);
    } else {
        prevobj.parent('td').html(prevobjval);
    }
}

function post(URL, PARAMS) {
    var temp = document.createElement("form");
    temp.action = URL;
    temp.method = "post";
    temp.style.display = "none";
    for (var x in PARAMS) {
        var opt = document.createElement("textarea");
        opt.name = x;
        opt.value = PARAMS[x];
        // alert(opt.name)        
        temp.appendChild(opt);
    }
    document.body.appendChild(temp);
    temp.submit();
    return temp;
}


function makePost($ar) {
    $val = '';
    $.each($ar, function(name, value) {
        $val += "'" + name + "':'" + value + "',";
    });
    $val = trim($val, ',');
    $val = "({" + $val + "})";
    $val = eval($val);
    return $val;
}

function gettag($obj) {
    if (typeof($obj) == 'object') {
        $name = $obj.attr('tag');
        ch = new Array;
        ch = $name.split(" ");
        if (ch.length > 1) {
            return 1;
        } else {
            return ch[0];
        }
    }
    return $obj;
}

function intag($obj, $str) {
    $name = $obj.attr('tag') + ' ';
    $str = $str + ' ';
    if ($name != undefined) {

        if ($name.indexOf($str) != -1) {
            return true;
        } else {
            return false;
        }

    }
    return false;
}
_img_process = function() {
    try {
        $data = arguments[0];

        if ($data.flag) {

            s = $data.data.split(",");
            $name = upimgid;
            $id = 'viewimg_' + $name;
            imgobj = $('#' + $id);

            $img = '';
            $.each(s, function($key, $value) {
                $style = " background: none repeat scroll 0 0 #fafafa; border: 1px solid #ebebeb;border-radius: 3px; cursor: pointer;display: block;margin-top: 10px;width: 80px;";
                $img += '<div style="float:;width:auto;"><div class="upimg_view" style="height:80px;width:80px;overflow:hidden;cursor:pointer;margin-top:5px;" ><img alt="点击查看大图" src="' + $value + '" width="80px" onclick="window.open(\'' + $value + '\');"/> </div><input type="hidden" value="' + $value + '" name="' + $name + '"/> <button type="button" style="' + $style + '" onclick="javascript:delobj($(this).parent(\'div\'));">删除</button></div>';
            });

            if (imgobj.html() == undefined) {
                var imgbox = '<dl class="lineD" id="' + $id + '"><dd>' + $img + '</dd><div style="clear:both"></div></dl>';
                obj.after(imgbox);
            } else {
                imgobj.html($img);

            }
            img_auto_size(imgobj.find('.upimg_view'), '80', '80');
        }
    } catch (e) {
        d(e);
    }
}
_img_process_more = function() {
    //上传最大图片数量
    $max = 5;
    try {
        // d(prevobj);
        $data = arguments[0];
        if ($data.flag) {
            s = $data.data.split(",");
            obj = prevobj;
            $name = upimgid;
            $id = 'viewimg_' + $name;
            $style = " background: none repeat scroll 0 0 #fafafa; border: 1px solid #ebebeb;border-radius: 3px; cursor: pointer;display: block;margin-top: 10px;width: 80px;";
            imgobj = $('#' + $id);
            $tpmax = $max;
            $tpnum = imgobj.find('[name="' + $name + '[]"]').index();
            $tpmax -= 1;
            if ($tpnum == -1) {
                $tpnum = 0;
            }
            $img = '';
            $usertp = 0;
            $.each(s, function($key, $value) {
                if (($tpmax - $tpnum) >= 0 && ($usertp <= ($tpmax - $tpnum))) {

                    $img += '<div style="float:;width:auto;margin-right:5px;"><div class="upimg_view" style="height:80px;width:80px;overflow:hidden;cursor:pointer;margin-top:5px;" ><img alt="点击查看大图" src="' + $value + '" width="80px" onclick="window.open(\'' + $value + '\');"/> </div><input type="hidden" value="' + $value + '" name="' + $name + '[]"/> <button type="button" style="' + $style + '" onclick="javascript:delobj($(this).parent(\'div\'));">删除</button></div>';
                    $usertp += 1;
                }
            });
            if ($tpnum >= $max) {
                showd('最多上传' + $max + '张图片');
                return 0;
            }
            if (imgobj.html() == undefined) {
                var imgbox = '<dl class="lineD" id="' + $id + '">' + $img + '</dl>';
                obj.after(imgbox);
            } else {
                imgobj.append($img);
            }
            img_auto_size(imgobj.find('.upimg_view'), '80', '80');
            $btn = $('.' + $name + '[tag="img_up_more"]');
            $btn.text('添加图片');
        }
    } catch (e) {
        d(e);
    }

}
_img_view = function() {

        try {
            $data = arguments[0];

            if ($data.flag) {

                s = $data.data.split(",");
                $name = upimgid;
                $id = 'viewimg_' + $name;
                imgobj = $('#' + $id);

                $img = '';
                $('img#' + $id).attr('src', s);
                $hidebox = $('[name=' + $name + ']');

                if ($hidebox.length) {
                    $hidebox.val(s);
                } else {
                    $img += '<input type="hidden" value="' + s + '" name="' + $name + '" />';
                    imgobj.after($img);
                }



            }
        } catch (e) {
            d(e);
        }
    }
    //有错误,不可以调用
function getdomobj(e) {
    e = e ? e : (window.event ? window.event : null);
    $new_obj = e.srcElement || e.target;
    return $new_obj;
}

function _form_submit($form_obj) {
    _from_statu = 0;
    checkform($form_obj);

    if (_from_statu) {
        $form_obj.submit();
        return false;
    } else {
        try {
            if ($('.oe_trap_error').index() != -1) {
                $(window).scrollTop($('.oe_trap_error').offset().top);
            }
            if ($('.oe_x').index() != -1) {
                $(window).scrollTop($('.oe_x').offset().top);
            }
        } catch (e) {
            d(e);
        }
        if (_from_statu === 0) {
            $form_obj.submit();
            return false;
        }
    }


    return false;
}

function stop_bubble($obj) {
    $obj.mousemove(function(event) {
        event.stopPropagation();
    });

}

function textback(data) {
    if (data.flag) {
        prevobj.parent().html(prevobj.val());

    } else {

        try {
            prevobj.parent().html(prevobjval);
            showd(data.error.errormsg)

        } catch (e) {}
    }

}
/*----------------------------------------内部成员初始化----------------------------------------*/
//文件上传对象
fileobj = new _upfile();
/*----------------------------------------可以调用函数----------------------------------------*/
//确认操作
function boxyndo($bool) {
    closebox();
    if (!$bool) {
        return false;
    } else {

        $obj = boxynobj;
        //清空全局变量的对象
        boxynobj = null;
        $do = $obj.attr('do');
        if ($do == undefined) {} else {
            eval($do);
            return false;
        }
        $a = $obj.attr('a');
        if ($a == undefined) {} else {

            _go_url($a);
            return false;
        }
    }
}

function boxyn($obj, $msg) {
    boxynobj = typeof(boxynobj) == 'object' ? $obj : $(this);
    $boxyn = $(__boxyn_html);
    if ($msg) {

    } else {
        $msg = '确认删除';
    }
    msgbox('<b style="font-size:14px;font-weight:normal;">' + $msg + '</b>', $boxyn, '200', null, null, null, 9999);
    return false;
}
//ajax检测的回调函数
function msgapi($obj, $msg, $flag) {
    change_form_statu($flag);
    $callback.call($obj, $msg, $flag);
}
//表单检测提示信息显示,0错误,1正常,-1普通消息
var form_msg_show = function($msg, $flag) {
    switch ($flag) {
        case undefined:
            $html = _form_error($msg);
            break;
        case 0:
            $html = _form_error($msg);
            break;
        case 1:
            $html = _form_ok($msg);
            break;
        case -1:
            $html = _form_msg($msg);
            break;
    }
    $(this).next('span').remove();
    $(this).after($html);

}

function tools_select(name, obj) {

    $('input[name=\'' + name + '\']').attr('checked', $(obj).hasClass('icon-checkbox-unchecked'));
    $(obj).toggleClass('icon-checkbox-unchecked');
    return false;
}

function addchild(val, text, $id) {

    if ($id) {
        $obj = $id;
    } else {}

    $a = $obj.find('[name=parentid]');
    $a.find("option:selected").attr('selected', '');
    $a.find("option[value=" + val + "]").attr('selected', 'selected');
    msgbox('添加[' + text + ']子菜单', $obj);
}

function tools_submit(obj) {

    /*ff = $('form[action=""]');*/
    ff = $('<form action=""></form>');
    confirm_flag = true;
    $(document.body).append(ff);
    if (obj != undefined) {
        ff.attr('action', obj['action']);
        ff.attr('method', 'post');

    }
    confirm_flag = true;
    if (confirm_flag) {
        var select_id = obj['id'];
        var o2 = '';
        if (obj['select_id'] != undefined) select_id = obj['select_id'];

        o2 = $("input[name='" + select_id + "[]']:checked");
        if ($("input[name='" + select_id + "[]']:checked").size() > 0) {

            o2.each(function(i, v) {


                ff.append(v);
            });


            ff.first().submit();
        } else {

            msgbox('消息提示', "<p class='warning'>没有选择任何项目，无法删除</p>", 200, 100, '50%', '50%');
        }

    }

    return false;
}

function d(msg) {
    if (D_DEBUG) {
        console.log(arguments.callee.caller);
    }

    console.log('****************');
    console.log(msg);
    console.log('****************');
}

function showd(msg) {
    alert(msg);
}

function trim(str, findstr) {
    $word = findstr;
    if ($word == undefined) {
        $word = '\\s*';
    } else {
        $word = ($word);
    }
    $find = new RegExp("(^" + $word + ")|(" + $word + "$)", "g");

    return str.replace($find, "");
}

function search_str(s) {
    if (s.length == 0) {
        alert('搜索关键词未填写！');
        return false;
    }
    //以下导致html重置;js对象丢失不可用
    s = encode(s);

    var obj = document.getElementsByTagName('body')[0];
    var t = obj.innerHTML.replace(/<span\s+class=.?oe_search_word.?>([^<>]*)<\/span>/gi, '$1');
    obj.innerHTML = t;
    var cnt = loopSearch(s, obj);
    t = obj.innerHTML
    var r = /{searchHL}(({(?!\/searchHL})|[^{])*){\/searchHL}/g
    t = t.replace(r, '<span class=\'oe_search_word\'>$1</span>');
    obj.innerHTML = t;
    $otop = $($('.oe_search_word').get(0)).offset().top;
    $(document).scrollTop($otop);
    alert('搜索到关键词' + cnt + '处')
}

function change_statu($jq_obj) {
    d($jq_obj);
    $status = $jq_obj.css('display');
    if ($status != 'none') {
        $jq_obj.css('display', 'none');
        return 0;
    } else {
        $jq_obj.css('display', '');
        $jq_obj.show();
        return 1;
    }
}

function load_area_box($depth_1_arr, $default_text, $name, $val, $level, $width) {
    area_level = $level;
    if ($('.oe_diqu').html() != undefined) {
        return;
    }
    $area_content = _load_area_child_hrml($depth_1_arr);
    $area_box = '<div class="oe_diqu" ><input name="' + $name + '" type="hidden" value="' + $val + '" id="oe_diqu_choose"></input>' +
        '<div class="oe_diqu_con" onclick=\'change_statu($(this).next(".oe_diqu_content"))\'>' + $default_text + '</div>' +
        ' <div class="oe_diqu_content" style="display:none;z-index:999">' +
        '<h6>' +
        ' <a href="#" class="current" onclick="_load_tab($(\'.oe_diqu_content h6 a\'),$(\'.oe_diqu_content .oe_diqu_1\'),\'current\',this)">' + __area_box_head.l1 + '</a>' +
        '<a href="#" onclick="_load_tab($(\'.oe_diqu_content h6 a\'),$(\'.oe_diqu_content .oe_diqu_1\'),\'current\',this)">' + __area_box_head.l2 + '</a>' +
        ' <a href="#" onclick="_load_tab($(\'.oe_diqu_content h6 a\'),$(\'.oe_diqu_content .oe_diqu_1\'),\'current\',this)">' + __area_box_head.l3 + '</a>' +
        ' <a href="#" style=" border-right:none;" onclick="_load_tab($(\'.oe_diqu_content h6 a\'),$(\'.oe_diqu_content .oe_diqu_1\'),\'current\',this)">' + __area_box_head.l4 + '</a>' +
        ' <div class="clear"></div>' +
        ' </h6>' +
        ' <div class="oe_diqu_1 current">' +
        $area_content + ' </div>' +
        ' <div class="oe_diqu_1 current" style="display:none;">' +
        '</div>' +
        ' <div class="oe_diqu_1 current" style="display:none;">' +
        '</div>' +
        ' <div class="oe_diqu_1 current" style="display:none;">' +
        '</div>' +
        '</div>' +
        '</div>';
    document.writeln($area_box);
    _area_obj = $('.oe_diqu');
    if ($level != undefined) {
        $level = $level - 1;
        _area_obj.find('.oe_diqu_content h6 a:gt( ' + $level + ')').remove();
        _area_obj.find('.oe_diqu_content .oe_diqu_1:gt( ' + $level + ')').remove();
    }
    if ($width != undefined) {
        $width2 = parseInt($width) + 10;
        _area_obj.css('width', $width);
        _area_obj.find('.oe_diqu_content').css('width', $width2);
    }


}

function ajax_get_area($val) {
    if (_tobool($val)) {

        yAjax(_ajax_area_url, {
            'parentid': $val
        }, function(data) {

            $data = data;
            if ($data.flag) {
                $obj = _area_obj.find('.oe_diqu_content h6 a');
                $index = _area_obj.find('.oe_diqu_content h6 a').index($('.oe_diqu_content h6 .current'));
                $html = _load_area_child_hrml($data.data);
                $(_area_obj.find('.oe_diqu_content .oe_diqu_1').get($index + 1)).html($html);

                $(_area_obj.find('.oe_diqu_content h6 a').get($index + 1)).click();
            } else {
                $(_area_obj.find('.oe_diqu_content .oe_diqu_1').get($index + 1)).html('');
                d($data.error);
            }


        });
    }
}

function msgbox(title, obj, width, height, left, top, index, overflow, background) {

    if (width == undefined || width == '') {
        width = '';
    }
    if (height == undefined || height == '') {
        height = '';
    }


    if (left == undefined || left == '') {
        left = ((1 - (obj.width() / $(document).width())) / 2) * 100 + '%';
    } else {
        left += 'px';
    }
    if (top == undefined || top == '') {
        top = ((1 - (obj.height() / $(document).height())) / 2) * 100 + '%';
    } else {
        top += 'px';
    }
    if (index == undefined || index == '') {
        index = '9999';
    } else {}
    if (overflow == undefined || overflow == '') {
        overflow = '';
    } else {
        overflow = 'hidden';
    }
    if (background == undefined || background == '') {
        background = '';
    } else {
        $bghtml = "<div style='position: fixed;width: 100%;height: 100%;top: 0px;left: 0px;background-color: rgba(0, 0, 0, 0.0470588);'></div>";


    }
    if (boxobj != null) {
        try {
            closebox();
        } catch (e) {

        }
    }
    $titlestyle = ' style="width:80%;margin-right:20px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" ';
    if (boxobj == null) {
        $boxs = '<div id="msgbox" class="move"  style="width:' + width + 'px;z-index:' + index + ';height:' + height + 'px;background-color: rgba(0, 0, 0, 0.05);position:fixed;left:' + left + ';top:' + top + ';padding:8px;overflow: ' + overflow + ';"><div class="mggbox_1">';
        $boxtitleend = '<div  id="boxclose" onclick="closebox()">×</div></div><div style="clear:both" class="msgclear"></div>';
        $boxhead = '<div class="msgtitle">';
        $boxtitle = '<div  id="boxtitle"' + $titlestyle + '>' + title + '</div>';
        $boxe = '</div></div>';
        if (typeof(obj) == 'object') {
            $boxbody = '<div style="overflow: ' + overflow + ';" id="boxbody">' + obj.html() + '</div>';

        } else {
            $boxbody = '<div style="overflow: ' + overflow + ';" id="boxbody">' + obj + '</div>';
        }
        if (title != '') {
            $box = $boxs + $boxhead + $boxtitle + $boxtitleend + $boxbody + $boxe;
        } else {
            $box = $boxs + $boxhead + $boxtitleend + $boxbody + $boxe;
        }

        $('body').prepend($box);

        boxobj = $('#msgbox');

    }


    boxobj.find('input:first').focus();
}
var yajaxobj = null;

function yAjax(ur, ar, $fun, $obj, $lock) {

    yajaxobj = $obj;
    _load_view_show();
    ar = makePost(ar);
    $async = $lock != undefined ? $lock : _ajax_asyn;
    $.ajax({
        url: ur,
        type: "POST",
        data: ar,
        dataType: 'json',
        async: $async,
        success: function(data, status) {

            _load_view_hide();
            if ($fun == null) {
                _ajax_recv(data);
            } else {
                $fun.call(this, data, status);
            }
        },
        error: function(data) {

            _load_view_hide();
            try {
                $info = jta(data.responseText);
                if ($info) {
                    if ($fun == null) {
                        _ajax_recv($info);
                    } else {
                        $fun.call(this, $info, status);
                    }
                } else {
                    d(data.responseText);
                }
            } catch (error) {
                $fun.call(this, data, status);
            }



        }
    });
}
var upimgid = null;

function upimg($url, $inputid, $cut_flag, $cut_option, $load_view_flag) {

    upimgid = $inputid;
    fileobj.url = $url;
    fileobj.fileobjid = $inputid;
    if (Boolean($load_view_flag)) {
        fileobj.load_show = $load_view_flag;
    }
    if ($('#' + $inputid).attr('multiple') != 'multiple') {
        if (_tobool($cut_flag)) {
            fileobj._call_back = _img_process_more;
        } else {
            fileobj._call_back = _img_process;
        }
        $fun = 'send';
    } else {
        fileobj._call_back = _img_process_more;
        $fun = 'sendmore';
    }
    eval('fileobj.' + $fun + '()');
}

function upimg2($url, $inputid, $load_view_flag, $funs) {

    upimgid = $inputid;
    fileobj.url = $url;
    fileobj.fileobjid = $inputid;
    if (Boolean($load_view_flag)) {
        fileobj.load_show = $load_view_flag;
    }

    if ($funs) {
        if (typeof($funs) == 'function') {
            fileobj._call_back = $funs;
        } else {
            fileobj._call_back = eval($funs);
        }


    } else {
        fileobj._call_back = _img_view;
    }

    $fun = 'send';
    yslook = 1;
    if (getimgsize($("#" + $inputid)) > MAX_YS_SIZE) {
        //大于1m的压缩在上传

        $obj = _ys_img_sc($inputid, $url, $funs);
    } else {
        //小于1m直接上传
        eval('fileobj.' + $fun + '()');
    }



}

function upfile($url, $inputid, $callback, $load_view_flag) {
    fileobj.url = $url;
    fileobj.fileobjid = $inputid;
    if (Boolean($load_view_flag)) {
        fileobj.load_show = $load_view_flag;
    }
    if ($('#' + $inputid).attr('multiple') != flase) {
        //单文件
        $fun = 'send';
    } else {
        //多文件
        $fun = 'sendmore';
    }
    fileobj._call_back = $callback;
    eval('fileobj.' + $fun + '()');
}

function refreshCc() {
    var ccImg = document.getElementById("checkCodeImg");
    if (ccImg) {
        ccImg.src = ccImg.src + '&' + Math.random();
    }
}

function closebox() {

    if (_tobool(boxobj)) {
        boxobj.remove();
    } else {
        try {
            //  d($(this).parent('').html());
            $('#msgbox').remove();
        } catch (e) {

        }

    }
    boxobj = null;
}

function checkform($obj) {

    //检测input
    $obj.find(':input[tag]:not(:hidden)').not('[tag=submit]').blur();

    //检测option
    /* $obj.find('select:not(:hidden)').not('[tag=submit]').change();
    //检测文本区域
    $obj.find('textarea:not(:hidden)').not('[tag=submit]').blur();*/
    //页面自动检测函数



    $bool = checkpage.call(this);
    if (typeof($bool) != 'undefined') {
        change_form_statu($bool);
        $callback.call(this, null, $bool);
    }

    return _from_statu;

}

function getbrowser() {
    if (navigator.userAgent.indexOf("Opera") != -1) {
        return ('opera');
    } else if (navigator.userAgent.indexOf("MSIE") != -1) {
        if ($.browser.msie && ($.browser.version == "6.0") && !$.support.style) {
            return ("ie6");
        } else if ($.browser.msie && ($.browser.version == "7.0")) {
            return ("ie7");
        } else if ($.browser.msie && ($.browser.version == "8.0")) {
            return ("ie8");
        } else if ($.browser.msie && ($.browser.version == "9.0")) {
            return ("ie9");
        }


        return ('ie');
    } else if (window.navigator.userAgent.toLowerCase().indexOf("360se") >= 1) {

        return ('360浏览器');

    } else if (navigator.userAgent.indexOf("Firefox") != -1) {
        return ('firefox');
    } else if (navigator.userAgent.indexOf("Netscape") != -1) {
        return ('netscape');
    } else if (navigator.userAgent.indexOf("Safari") != -1) {
        return ('safari');
    } else {
        return ('无法识别的浏览器。');
    }
}

function dianping(xy, obj, name) {
    obj = $(obj);
    x = xy.clientX;
    boxx = obj.offset().left;
    $range = x - boxx;
    $width = obj.width();
    $p = $width / 5;
    $score = 0;
    if ($range < ($p * 1)) {
        $score = 1;
    } else if (($p * 1) < $range && $range < ($p * 2)) {
        $score = 2;
    } else if (($p * 2) < $range && $range < ($p * 3)) {
        $score = 3;
    } else if (($p * 3) < $range && $range < ($p * 4)) {
        $score = 4;
    } else if (($p * 4) < $range && $range < ($p * 5)) {
        $score = 5;
    }
    obj.attr('class', 'star_' + $score);

    $('[name=' + name + ']').val($score);
    $('[tag="' + name + '"]').text($score);
}

function setword(str, obj) {

    if (obj.value == str) obj.value = '';
    $(obj).blur(function() {

        if (obj.value == '') obj.value = str;

    });
}

function showobj(obj) {

    obj.show();
}

function hideobj(obj) {

    obj.hide();
}
//要复制的对象,插入的对象,是否清空默认值
function addhtml($html_obj, $append_obj, $clear_flag) {
    try {
        $html = $html_obj.get(0).outerHTML;
        //复制时清空原来的值
        $append_obj.append($html);
        $clear_flag = _tobool($clear_flag);
        if (!$clear_flag) {
            $addobj = $append_obj.children().last();

            $addobj.find('input[type=text]').not($('[type=button]')).val('');
            $addobj.find('textarea').text('');

        }
    } catch (e) {
        d(e);
    }

}

var tmpmsgid = null;
var dsq = null;

function _closeshowmsg() {

    tmpmsgid.remove();
}

function _showmsg($msg, $time, $jqobj, $event) {
    if (typeof($jqobj) == 'object') {

    } else {
        $jqobj = $(this);
    }
    //坐标定位
    $x = $event.pageX;
    $y = $event.pageY;
    //$style = 'display:none';
    $style = 'display:';
    $html = "<div class='tmp_msg' style='" + $style + "'>" + $msg + "</div>";

    tmpmsgid = $('.tmp_msg');
    if (tmpmsgid.html() == undefined) {
        $('body').append($html);
        tmpmsgid = $('.tmp_msg');
    }
    tmpmsgid.offset({ 'top': $y, 'left': $x - 5 });
    dsq = setTimeout('_closeshowmsg()', $time);

    //移开马上消失,定时器清零
    $jqobj.live('mouseout', function() {
        _closeshowmsg();
        clearTimeout(dsq);
    });
}

function showmsg($msg, $time, $jqobj, $event) {
    $time = $time / 2;
    dsq = setTimeout(_showmsg, $time, $msg, $time, $jqobj, $event);


}
//载入切换;操作的父容器;控制的容器;父容器样式名变化,控制的框元素样式变化
function loadtab($czdiv, $kzdiv, $class, $cgclass) {
    $cz = $czdiv.children();

    $kz = $kzdiv.children();

    $bool = 0;
    if ($cgclass == undefined) {
        $bool = 1;
    }
    if ($bool) {
        $kz.hide();
    } else {
        $kz.removeClass($cgclass);
    }

    $($kz.get(0)).show();
    $cz.bind('click', function() {

        $dq = $cz.index($(this));
        $cz.removeClass($class);
        $(this).addClass($class);
        if ($bool) {
            $kz.hide();

            $($kz.get($dq)).show();
        } else {
            $kz.removeClass($cgclass);
            $($kz.get($dq)).addClass($cgclass);
        }

    });
}
//把css转成对象
function selectStyle(sel) {
    if (sel.substr(0, 1) != ".") {
        sel = "." + sel;
    }

    for (var cont = 0; cont < document.styleSheets.length; cont++) {
        v = document.styleSheets[cont];

        attrClass = selectAttr(sel, v);

        if (attrClass != false) {
            break;
        }

    }

    if (!attrClass) {
        attrClass = Array();
    }

    objStyle = {}

    if (attrClass == "") {
        return false;
    }

    if (attrClass.match(";")) {
        attrClass = attrClass.split(";");
    } else {
        attrClass = [attrClass];
    }

    $(attrClass).each(function(i, v) {
        if (v != "") {
            v = v.split(":");
            v[0] = toCamelCase(v[0]);

            objStyle[v[0]] = $.trim(v[1]);

        }
    });
    return objStyle;
}

function selectAttr(sel, v) {

    attrClass = false;

    if ($.browser.msie) {
        if (v.rules.length > 0) {
            $(v.rules).each(function(i2, v2) {
                if (sel == v2.selectorText) {
                    attrClass = v2.style.cssText;
                }
            });
        } else if (v.imports.length > 0) {
            $(v.imports).each(function(i2, v2) {

                if (sel == v2.selectorText) {
                    attrClass = v2.style.cssText;
                } else if (v2 == "[object]" || v2 == "[Object CSSStyleSheet]" || v2 == "[object CSSImportRule]") {
                    return selectAttr(sel, v2);
                }
            });
        }
    } else {

        $(v.cssRules).each(function(i2, v2) {
            if (sel == v2.selectorText) {
                attrClass = v2.style.cssText;
            } else if (v2 == "[object CSSImportRule]") {
                return selectAttr(sel, v2.styleSheet);
            }
        });
    }

    return attrClass;
}

function toCamelCase(str) {
    str = $.trim(str);
    str = str.replace(/-/g, " ");
    str = str.toLowerCase();

    strArr = str.split(" ");

    var nStr = "";
    $(strArr).each(function(i, v) {
        if (i == 0) {
            nStr += v;
        } else {
            /*
            v = v.split("");
            v[0] = v[0].toUpperCase();
            nStr += v.join();
            */

            //There was a bug in older version, this correction was sugested by Simon Shepard.
            nStr += v.substr(0, 1).toUpperCase();
            nStr += v.substr(1, v.length);
        }
    });

    return nStr;
}
//添加动画对象,class变化,或者值
function dh($obj, $class, duration, easing, callback) {
    //如果是对象则直接调用,否则查找css组成对象在调用

    if (typeof($class) != 'object') {
        $classname = $class;
        $class = selectStyle($class);
    }

    if (typeof($class) == 'object') {

        $obj.animate($class, duration, easing, callback);

    }
    if (typeof($classname) != 'undefined') {
        $obj.addClass($classname);
    }
    return;
}
//鼠标是否移出容器

/*----------------------------------------自动加载函数----------------------------------------*/
function gobuttom($obj) {

    $obj.scrollTop($obj[0].scrollHeight);
}

function music_play($musicsrc) {
    var borswer = window.navigator.userAgent.toLowerCase();
    $audio = $musicsrc;
    if (borswer.indexOf("ie") >= 0) {
        //IE内核浏览器
        var strEmbed = '<embed name="embedPlay" id="audioPlay" src="' + $audio + '" autostart="true" hidden="true" loop="false"></embed>';
        if ($("body").find("#audioPlay").length <= 0) {
            $("body").append(strEmbed);
            var embed = document.embedPlay;
            embed.volume = 100;
        }

    } else {
        //非IE内核浏览器
        var strAudio = "<audio id='audioPlay'  src='" + $audio + "' hidden='true'>";
        if ($("body").find("#audioPlay").length <= 0) {
            $("body").append(strAudio);
            var audio = document.getElementById("audioPlay");
            audio.play();
        }

    }
    setTimeout(function() {
        $("body").find("#audioPlay").remove();

    }, 3000)

    return true;


}
$(function() {
    fun = '_jqformdeal';

    //这里设置回调函数
    $(document).on('submit', '[tag=jqform],[tag=jqfrom]', function() {
        if ($(this).attr('tag') == 'jqform' || $(this).attr('tag') == 'jqfrom') {
            if ($(this).attr('fun') != undefined) {
                fun = $(this).attr('fun');
            }
            if ($(this).attr('fun')) {
                fun = $(this).attr('fun');
            }
            ajaxSubmit($(this), fun);
            return false;
        } else {
            $(this).submit();
        }

    });
    $(document).on('mouseover ', '[tag=submit]', function() {
        if (isleave) {
            isleave = 0;
            $(this).focus();
        }
    });
    $(document).on('click ', '[tag=submit]', function() {

        $obj = _find_parent($(this), 'form');

        _form_submit($obj);

    });
    var $focusobj = null;
    var isleave = 0;
    $(document).on('focus', 'input', function() {
        $focusobj = this;
        $(this).bind('mouseleave', function($event) {
            $(this).unbind('mouseleave');
            if ($focusobj != this) {
                return;
            }
            //d(document);
            //$(this).nextAll('input').first().focus();
            //移动到了tag=submit附近则聚焦submit按钮;
            isleave = 1;
        });

    });
    $(document).on('click', '[tag=reset]', function() {
        $obj = _find_parent($(this), 'form');
        $read = $obj.find("[readonly=readonly]");
        //$read.attr('readonly', false);
        $read.attr('value', '');
        $obj.find("input").filter('[type=text]').attr('value', '');
        $obj.find(":hidden").not('[name=sflag]').val('');
        $obj.get(0).reset();
    });
    $(document).on('click', '[tag=submit_uncheck]', function() {
        $obj = _find_parent($(this), 'form');
        $obj.submit();

    });

    $(document).on('click', '[tag=back]', function() {
        $url = $(this).attr('url');
        if (_tobool($url)) {

            window.location = $url;
        } else {
            window.history.back();
        }



    });
    $(document).on('click', '[tag=go]', function() {

        $url = $(this).attr('url');
        _go_url($url);

    });

});

//处理jq提交的form的数据
$(function() {
    //创建富文本编辑器
    $('[tag="edit"]').each(function() {
        this.contentEditable = true;
        $name = $(this).attr('name');
        $inputhtml = $("<input type='hidden' name='" + $name + "'/>");
        $(this).after($inputhtml);

        $(this).live('DOMNodeInserted', function() {
            $inputhtml.val($(this).html());

        });
        $(this).live('keypress', function() {
            $inputhtml.val($(this).html());

        });
        $(this).live('keyup', function() {
            $inputhtml.val($(this).html());

        });
        $(this).live('keydown', function() {
            $inputhtml.val($(this).html());

        });
        /*d($inputhtml.val());*/
    });
    $('[tag="img_up"]').each(function() {

        if (!$(this).attr('imgflag')) {
            $name = $(this).attr('imgname');
            $url = $(this).attr('url');
            $fun = $(this).attr('fun');
            $upfile_html_obj = $('#' + $name);
            $(this).attr('imgflag', 1);



            if ($upfile_html_obj.html() == undefined) {
                $(this).parent().css('position', 'relative');
                $hight = $(this).outerHeight() + $(this).position().top;
                $w = $(this).outerWidth() + $(this).position().left;

                $style = "text-align:right;padding-right:200px;opacity:0;position:absolute;top:0;left:0px;width:" + $w + "px;z-index:120;height:" + $hight + "px;filter:Alpha(opacity=0);cursor:pointer;";


                if ($url == undefined) {

                    $url = _ajax_file_url;
                }
                if ($fun) {
                    $click = 'upimg2(\"' + $url + '\",\"' + $name + '\",null,"' + $fun + '")';
                } else {
                    $click = 'upimg2(\"' + $url + '\",\"' + $name + '\")';
                }


                $html = '<input id="' + $name + '" name="tmp_up_' + $name + '" type="file" accept="image/*" value="" style="' + $style + '" onchange=' + $click + '>';
                $(this).before($html);

                //添加显示框




            } else {
                //检测位置
                $dw = {
                    top: $(this).offset().top,
                    left: $(this).offset().left
                };
                $upfile_html_obj.offset($dw);

            }




        }

    });





});
$(document).on('mouseover', 'button', function() {

    $tagname = $(this).attr('tag');
    $tmd = 0;
    switch ($tagname) {
        case 'img_up_one':

            prevobj = $(this);
            $name = $(this).attr('class');
            $url = $(this).attr('url');
            $cut_flag = $(this).attr('cut_flag');
            $cut_option = $(this).attr('option');
            if (typeof($cut_option) == 'string') {
                $cut_option = _objtostr(_strtoobj($cut_option));
            }

            //上传按钮
            $upfile_html_obj = $('#' + $name);
            if ($upfile_html_obj.html() == undefined) {
                if (+[1, ] && !window.VBArray) {
                    $style = "display: none";


                } else {
                    $(this).parent().css('position', 'relative');
                    $hight = $(this).outerHeight() + $(this).position().top;
                    $w = $(this).outerWidth() + $(this).position().left;
                    $style = "text-align:right;padding-right:200px;opacity:" + $tmd + ";position:absolute;top:0;left:0px;width:" + $w + "px;z-index:120;height:" + $hight + "px;filter:Alpha(opacity=0);cursor:pointer;";
                }

                if ($url == undefined) {

                    $url = _ajax_file_url;
                }
                $click = 'upimg(\"' + $url + '\",\"' + $name + '\",\"' + $cut_flag + '\",\"' + $cut_option + '\")';
                $html = '<input id="' + $name + '" name="tmp_up_' + $name + '" type="file" accept="image/*" value="" style="' + $style + '" onchange=' + $click + '>';
                $(this).before($html);
                //添加显示框
                if ($('#viewimg_' + $name).html()) {

                } else {
                    $showhtml = '<span id="viewimg_' + $name + '"></span>'
                    $(this).after($showhtml);
                }


            } else {
                //检测位置
                $dw = {
                    top: $(this).offset().top,
                    left: $(this).offset().left
                };
                $upfile_html_obj.offset($dw);

            }
            /* d($upfile_html_obj.parent().css('position'));*/
            //传递点击事件给上传按钮
            $upfile_html_obj = $('#' + $name);
            if (+[1, ] && !window.VBArray) {
                /*   _click($upfile_html_obj);*/


            } else {
                // showd('ie下请点击新增的控件');
                //ie显示上传文件控件;必须手点,不然ie权限会无法访问控制的文件资源;
                //  $upfile_html_obj.css('display', 'block');
            }

            ;
            break;
        case 'img_up_more':
            prevobj = $(this);
            $name = $(this).attr('class');
            $url = $(this).attr('url');
            $cut_flag = $(this).attr('cut_flag');
            $cut_option = $(this).attr('option');
            if (typeof($cut_option) == 'string') {

                $cut_option = _objtostr(_strtoobj($cut_option));

            }

            //上传按钮
            $upfile_html_obj = $('#' + $name);
            if ($upfile_html_obj.html() == undefined) {
                if (+[1, ] && !window.VBArray) {
                    $more = 'multiple = "multiple"';
                    $style = "display: none";
                } else {
                    $more = ' ';
                    $(this).parent().css('position', 'relative');
                    $hight = $(this).outerHeight() + $(this).position().top;
                    $w = $(this).outerWidth() + $(this).position().left;

                    $style = "text-align:right;padding-right:200px;opacity:" + $tmd + ";position:absolute;top:0;left:0px;width:" + $w + "px;z-index:120;height:" + $hight + "px;filter:Alpha(opacity=0);cursor:pointer;";
                }
                if ($url == undefined) {
                    $url = _ajax_file_url;
                }
                $click = 'upimg(\"' + $url + '\",\"' + $name + '\",\"' + '1' + '\",\"' + $cut_option + '\")';
                $html = '<input id="' + $name + '" name="tmp_up_' + $name + '" type="file" ' + $more + ' accept="image/*" value="" style="' + $style + '" onchange=' + $click + '>';
                $(this).before($html);
            } else {
                //检测位置
                $dw = {
                    top: $(this).offset().top,
                    left: $(this).offset().left
                };
                $upfile_html_obj.offset($dw);

            }
            //传递点击事件给上传按钮
            $upfile_html_obj = $('#' + $name);
            if (+[1, ] && !window.VBArray) {
                /*  _click($upfile_html_obj);*/


            } else {
                // showd('ie下请点击新增的控件');
                //ie显示上传文件控件;必须手点,不然ie权限会无法访问控制的文件资源;
                //  $upfile_html_obj.css('display', 'block');
            }

            ;
            break;
        case 'file_up_more':
            ;
            break;
        case 'file_up_more':
            ;
            break;
    }
});


$(document).on('click', 'button', function() {
    $tagname = $(this).attr('tag');
    $name = $(this).attr('class');
    switch ($tagname) {
        case 'img_up_one':

            prevobj = $(this);
            //传递点击事件给上传按钮
            $upfile_html_obj = $('#' + $name);

            if (+[1, ] && !window.VBArray) {

                _click($upfile_html_obj);


            } else {
                showd('ie下请点击新增的控件');
                //ie显示上传文件控件;必须手点,不然ie权限会无法访问控制的文件资源;
                //  $upfile_html_obj.css('display', 'block');
            }

            ;
            break;
        case 'img_up_more':
            prevobj = $(this);

            $upfile_html_obj = $('#' + $name);
            if (!+[1, ]) {
                $upfile_html_obj.css('display', 'block');
            } else {
                _click($upfile_html_obj);
            }

            ;;
            break;
        case 'file_up_more':
            ;
            break;
        case 'file_up_more':
            ;
            break;
    }
});

$(function() {
    $('td,a').mouseover(function() {
        $classname = $(this).attr('tag');

        $who = '';
        if ($classname != undefined) {
            switch ($classname) {
                case 'showimg':
                    $src = $(this).attr('img');
                    $img = '<img src="' + $src + '" style="width:150px"/>';
                    msgbox('图片预览', $img, null, null, $(this).offset().left + 50, $(this).offset().top, 666, 1);
                    var t = setTimeout(" closebox();", 5000)
                    $(this).mouseout(function() {
                        clearTimeout(t);
                        closebox();
                    });;
                    break;
                case '':

                    ;
                    break;

            }
        }
    });
    $('td').mouseover(function(e) {

        $ev = e || window.event;
        $msg = '单击进行编辑';
        $time = 2000;
        $classname = $(this).attr('tag') || $(this).attr('clas');
        $who = '';
        if ($classname != undefined) {
            switch ($classname) {
                case 'ajaxtext':
                    $(this).css('cursor', 'pointer');

                case 'ajaxchoose':
                    $(this).css('cursor', 'pointer');


                    ;
                    break;
                case '':
                    ;
                    break;
            }
        }
    });

    $('td').click(function() {
        $classname = $(this).attr('tag') || $(this).attr('clas');
        $who = '';
        if ($classname != undefined) {
            switch ($classname) {
                case 'ajaxtext':
                    if ($(this).children('input').html() == undefined) {
                        prevobjval = $(this).text();

                        $name = $(this).attr('name');
                        $key = $(this).attr('key');
                        $text = $(this).text();
                        $find = new RegExp('\\|[\\s\\|\\-]*', "g");
                        $text = $text.replace($find, '');
                        $text = trim($text);
                        $who = $(this).attr('who');
                        $input = '<input  style="z-index:999" type="text" name="' + $name + '" key="' + $key + '" value="' + $text + '" onblur="fastajax(this,null,textback)" >';
                        if ($who != '') {
                            $input = '<input  style="z-index:999" type="text" name="' + $name + '" key="' + $key + '" value="' + $text + '" who="' + $who + '" onblur="fastajax(this,null,textback)" >';
                        }
                        $(this).html($input);

                        prevobj = $(this).children('input');

                        prevobj.on('keydown', function(event) {
                            //  d(event.keyCode);
                            if (event.keyCode == 13) {
                                //  d('回车了');
                                //触发离开事件
                                this.onblur();
                                return false;
                            }
                        });
                        $(this).children('input').focus();
                    };
                    break;
                case 'ajaxchoose':

                    if ($(this).children('div').html() == undefined) {

                    } else {

                        prevobj = $(this);
                        $child = $(this).children('div');
                        $value = $child.attr('class');
                        //yes本身是值1
                        //no是值0

                        //因为要改变状态，所以取反
                        switch ($value) {
                            case 'yes':
                                $value = 0;
                                break;
                            case 'no':
                                $value = 1;
                                break;
                            case 'stop':
                                $value = 2; //保留
                                break;
                        }

                        fastajax(this, $value, cgdivflag);

                    }


                    ;
                    break;
                case '':
                    ;
                    break;
            }
        }
    });
});
$(function() {
    try {

        $("input.date").manhuaDate({
            Event: "click", //可选
            Left: 0, //弹出时间停靠的左边位置
            Top: -16, //弹出时间停靠的顶部边位置
            fuhao: "-", //日期连接符默认为-
            isTime: false, //是否开启时间值默认为false
            beginY: 1949, //年份的开始默认为1949
            endY: 2100 //年份的结束默认为2049
        });
    } catch (err) {}


    /*   $("input.date").attr('readonly', true);*/

});
$(function() {
    try {
        $.datetimepicker.setLocale('ch');
        $('input.date2').datetimepicker({
            lang: 'ch',
            format: 'Y-m-d H:i'
        });

        $('input.time').datetimepicker({
            datepicker: false,
            format: 'H:i',
            step: 5
        });
        $('input.date1').datetimepicker({
            lang: 'ch',
            timepicker: false,
            format: 'Y-m-d'

        });


    } catch (err) {}


    /*  $("input.date1,input.date2,input.time").attr('readonly', true);*/

});
$(document).on('mousedown', '.move', function(event) {


    var obj = $(this);

    $left = obj.offset().left - $(document).scrollLeft();
    $top = obj.offset().top - $(document).scrollTop();
    $css = {
        'top': $top,
        'left': $left,
        'position': 'fixed',
        'cursor': 'move'
    };
    obj.css($css);

    $top = obj.css('top').replace('px', '');

    $offtop = (event.clientY - $top);

    if ($offtop > 35) {} else {
        // if(0){}else{
        ofy = (event.clientY - obj[0].offsetTop);
        ofx = (event.clientX - obj[0].offsetLeft);


        var patch = parseInt($(this).css("height")) / 2; /* 也可以写成var patch=parseInt($(this).css("width"))/2*/
        $(document).mousemove(function(event) {

            var ox = event.clientX;
            var oy = event.clientY;
            //获取点到弹出框本身的位置


            // $("p:last").offset({ top: ofy, left: ofx });
            obj.css('top', oy - ofy);
            obj.css('left', ox - ofx);
        });

        $(document).mouseup(function() {
            $('.move').css('cursor', 'default');
            $(this).unbind("mousemove");
        });
    }


});
$(document).on('focus', ':input', function(event) {
    $(document).mouseout(function() {
        $(this).blur();
    });


});
$(document).on(' blur', ':input', function(event) {
    //判断是否聚焦;如果聚焦则执行;否则不执行
    //var focusedElement = document.activeElement;
    //$focus = $(focusedElement);

    //if ($focus.index(this))
    //{
    //    return;
    //}
    //处理这些问题的回调
    //event.stopPropagation();
    $callback = form_msg_show;
    $formbool = true;
    $form = _find_parent($(this), 'form');
    $msg = '';
    if (intag($(this), 'notnull')) {

        if ($(this).val() == null || $(this).val() == '') {
            $msg = '不能为空';
            change_form_statu(false);
            $callback.call(this, $msg, 0);
            return 0;
        } else {

            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    if (intag($(this), 'cannull')) {

        if ($(this).val() == null || $(this).val() == '') {
            // $msg = '不能为空';
            // change_form_statu(true);

            //  $callback.call(this, $msg);
            return 0;
        } else {

            // change_form_statu(true);
            // $callback.call(this, $msg, 1);
        }
    }

    if (intag($(this), 'notnum')) {

        if (!/\D/.test($(this).val())) {
            $msg = '不能有数字';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;

        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    //复杂度检测
    if (intag($(this), 'iscomplex')) {

        var strongRegex = new RegExp("^(?=.{7,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\W).*$", "g");
        var mediumRegex = new RegExp("^(?=.{6,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
        var enoughRegex = new RegExp("(?=.{5,}).*", "g");
        if (false == enoughRegex.test($(this).val())) {
            change_form_statu(false);
            $callback.call(this, '密码字符种类太少');
            return 0;
        } else if (strongRegex.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, '密码强度强', 1);
            return 0;
        } else if (mediumRegex.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, '密码强度中等', 1);
            return 0;
        } else {
            change_form_statu(false);
            $callback.call(this, '密码强度太弱');
            return 0;
        }

    }
    if (intag($(this), 'notcn')) {
        if (!/[^\u4E00-\u9FA5]/.test($(this).val())) {
            $msg = '不能有中文';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;

        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    if (intag($(this), 'isqq')) {
        if (!/^[1-9][0-9]{4,9}$/.test($(this).val())) {
            $msg = '请填写正确的qq号码';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;
        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }

    if (intag($(this), 'noten')) {

        if (!/\W/.test($(this).val())) {
            $msg = '不能有字母';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;
        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    if (intag($(this), 'max')) {
        $num = $(this).attr('num');
        if (($(this).val().length > $num)) {
            change_form_statu(false);
            $msg = '最多输入' + $num + '个字符';
            $callback.call(this, $msg);
            return 0;
        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    //小于等于
    if (intag($(this), 'lte')) {
        $num = parseInt($(this).attr('num'));

        if ((parseInt($(this).val()) > $num)) {
            change_form_statu(false);
            $msg = '不能大于' + $num + '';
            $callback.call(this, $msg);
            return 0;
        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    //大于等于;否则错误
    if (intag($(this), 'gte')) {
        $num = parseInt($(this).attr('num'));
        if ((parseInt($(this).val()) < $num)) {
            change_form_statu(false);
            $msg = '不能小于' + $num + '';
            $callback.call(this, $msg);
            return 0;
        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }

    if (intag($(this), 'min')) {
        $num = $(this).attr('num');
        if (($(this).val().length < $num)) {
            change_form_statu(false);
            $msg = '最少输入' + $num + '个字符';
            $callback.call(this, $msg);
            return 0;
        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    if (intag($(this), 'in')) {
        $maxnum = $(this).attr('max');
        $minnun = $(this).attr('min');
        if (($(this).val().length < $minnun)) {
            change_form_statu(false);
            $msg = '最少输入' + $minnun + '个字符';
            $callback.call(this, $msg);
            return 0;
        } else if (($(this).val().length > $maxnum)) {
            change_form_statu(false);
            $msg = '最多输入' + $maxnum + '个字符';
            $callback.call(this, $msg);
            return 0;
        } else {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        }
    }
    if (intag($(this), 'ismail')) {
        if (/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '邮箱格式不正确';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;

        }
    }
    if (intag($(this), 'isurl')) {
        $regexp = new RegExp("(http[s]{0,1}|ftp)://[a-zA-Z0-9\\.\\-]+\\.([a-zA-Z]{2,4})(:\\d+)?(/[a-zA-Z0-9\\.\\-~!@#$%^&*+?:_/=<>]*)?", "gi");
        if ($regexp.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = 'url格式不正确';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;


        }
    }
    if (intag($(this), 'isnumencn')) {
        $regexp = new RegExp("^[0-9a-zA-Z\u4e00-\u9fa5]+$", "gi");
        if ($regexp.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '不能含有特殊字符';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;

        }
    }

    if (intag($(this), 'isnumen')) {

        $regexp = new RegExp("^[0-9a-zA-Z_]+$", "gi");
        if ($regexp.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '只能包含字母,数字以及下划线';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;
        }
    }
    if (intag($(this), 'isnum')) {
        $regexp = new RegExp("^[0-9.]+$", "gi");
        if ($regexp.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '只能包含数字';
            change_form_statu(false);
            $callback.call(this, $msg);
            d(_from_statu);
            return 0;
        }
    }
    if (intag($(this), 'isequal')) {
        $last = $(this).attr('name').substring(1);


        $lastval = $form.find('[name=' + $last + ']').val();

        if ($(this).val() == $lastval) {
            $msg = '确认密码输入正确';
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '两次输入值不一致';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;
        }
    }
    if (intag($(this), 'isphone')) {
        if (/(^[0-9]{3,4}\-[0-9]{3,8}$)|(^[0-9]{3,8}$)|(^\([0-9]{3,4}\)[0-9]{3,8}$)|(^0{0,1}13[0-9]{9}$)/.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '电话号码格式不正确';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;

        }

    }
    if (intag($(this), 'istime')) {
        if (/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '时间格式不正确';
            change_form_statu(false);
            $callback.call(this, $msg);
            return 0;

        }

    }
    if (intag($(this), 'ismobile')) {

        if (/^(0|86|17951)?(13[0-9]|15[012356789]|17[012356789]|18[0-9]|14[57])[0-9]{8}$/.test($(this).val())) {
            change_form_statu(true);
            $callback.call(this, $msg, 1);
        } else {
            $msg = '手机号码格式不正确';
            change_form_statu(false);
            $callback.call(this, $msg);

            return 0;
        }
    }

});

$(document).on('keyup', 'input', function() {
    str = $(this).val();
    if (intag($(this), 'fixnum')) {

        $(this).val(str.replace(/[^\d|^\.]/gi, ""));
    }
    if (intag($(this), 'fixen')) {

        $(this).val(str.replace(/\W/gi, ""));
    }
    if (intag($(this), 'card')) {
        $(this).val(str.replace(/[^\d|^\.]/gi, ""));
        if (this.value.length > 25) {
            this.value = this.value.substr(0, 25);
            return 0;
        }
        if (!isNaN(this.value.replace(/[ ]/g, ""))) {
            this.value = this.value.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, "$1 "); //四位数字一组，以空格分割

        } else {
            if (e.keyCode == 8) { //当输入非法字符时，禁止除退格键以外的按键输入
                return true;
            } else {
                return false
            }
        }



    }
    if (intag($(this), 'fixcn')) {

        $(this).val(str.replace(/[^\u4E00-\u9FA5]/gi, ""));
    }

});

$(document).on('keydown', 'input', function(event) {

    if (event.keyCode == 13 && intag($(this), 'enter')) {
        $form = _find_parent($(this), 'form');
        //触发离开事件
        $(this).blur();

        if ($('[tag=submit]').index() < 0) {
            $form.submit();
        } else {
            $('[tag=submit]').click();
        }


        return false;
    }
});

/*----------------------------------------ajax相关函数----------------------------------------*/
/*
jq动画扩展
*/

(function($) {
    $.fn.extend({
        animateToClass: function(to, duration, easing, callback) {
            if (!to) {
                return this;
            }

            styles = selectStyle(to);

            if (!styles) return this;

            return this.animate(styles, duration, easing, callback);
        }
    });

    function selectStyle(sel) {
        if (sel.substr(0, 1) != ".") {
            sel = "." + sel;
        }

        for (var cont = 0; cont < document.styleSheets.length; cont++) {
            v = document.styleSheets[cont];

            attrClass = selectAttr(sel, v);
            if (attrClass != false) {
                break;
            }

        }

        if (!attrClass) {
            attrClass = Array();
        }

        objStyle = {}

        if (attrClass == "") {
            return false;
        }

        if (attrClass.match(";")) {
            attrClass = attrClass.split(";");
        } else {
            attrClass = [attrClass];
        }

        $(attrClass).each(function(i, v) {
            if (v != "") {
                v = v.split(":");
                v[0] = toCamelCase(v[0]);

                objStyle[v[0]] = $.trim(v[1]);

            }
        });
        return objStyle;
    }

    function selectAttr(sel, v) {
        attrClass = false;

        if ($.browser.msie) {
            if (v.rules.length > 0) {
                $(v.rules).each(function(i2, v2) {
                    if (sel == v2.selectorText) {
                        attrClass = v2.style.cssText;
                    }
                });
            } else if (v.imports.length > 0) {
                $(v.imports).each(function(i2, v2) {

                    if (sel == v2.selectorText) {
                        attrClass = v2.style.cssText;
                    } else if (v2 == "[object]" || v2 == "[Object CSSStyleSheet]" || v2 == "[object CSSImportRule]") {
                        return selectAttr(sel, v2);
                    }
                });
            }
        } else {
            $(v.cssRules).each(function(i2, v2) {
                if (sel == v2.selectorText) {
                    attrClass = v2.style.cssText;
                } else if (v2 == "[object CSSImportRule]") {
                    return selectAttr(sel, v2.styleSheet);
                }
            });
        }

        return attrClass;
    }

    function toCamelCase(str) {
        str = $.trim(str);
        str = str.replace(/-/g, " ");
        str = str.toLowerCase();

        strArr = str.split(" ");

        var nStr = "";
        $(strArr).each(function(i, v) {
            if (i == 0) {
                nStr += v;
            } else {
                /*
                v = v.split("");
                v[0] = v[0].toUpperCase();
                nStr += v.join();
                */

                //There was a bug in older version, this correction was sugested by Simon Shepard.
                nStr += v.substr(0, 1).toUpperCase();
                nStr += v.substr(1, v.length);
            }
        });

        return nStr;
    }
})(jQuery);



function formattime(now) {

    now = new Date(parseInt(now) * 1000);

    var year = now.getFullYear();

    var month = now.getMonth() + 1;
    var date = now.getDate();
    var hour = now.getHours();
    var minute = now.getMinutes();
    var second = now.getSeconds();
    return year + "-" + month + "-" + date + " " + hour + ":" + minute + ":" + second;
}

function rmb(num) {
    num = num.toString().replace(/\$|\,/g, '');
    if (isNaN(num))
        num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num * 100 + 0.50000000001);
    cents = num % 100;
    num = Math.floor(num / 100).toString();
    if (cents < 10)
        cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
        num = num.substring(0, num.length - (4 * i + 3)) + ',' +
        num.substring(num.length - (4 * i + 3));
    return (((sign) ? '' : '-') + num + '.' + cents);
}


/**
*BASE64 Encode and Decode By UTF-8 unicode
*可以和java的BASE64编码和解码互相转化
var base64 = BASE64.encoder(str);//返回编码后的字符  
  
var unicode= BASE64.decoder(base64Str);//返回会解码后的unicode码数组。 
*/
(function() {
    var BASE64_MAPPING = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f',
        'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
        'w', 'x', 'y', 'z', '0', '1', '2', '3',
        '4', '5', '6', '7', '8', '9', '+', '/'
    ];

    /**
     *ascii convert to binary
     */
    var _toBinary = function(ascii) {
        var binary = new Array();
        while (ascii > 0) {
            var b = ascii % 2;
            ascii = Math.floor(ascii / 2);
            binary.push(b);
        }
        /*
        var len = binary.length;
        if(6-len > 0){
        for(var i = 6-len ; i > 0 ; --i){
        binary.push(0);
        }
        }*/
        binary.reverse();
        return binary;
    };

    /**
     *binary convert to decimal
     */
    var _toDecimal = function(binary) {
        var dec = 0;
        var p = 0;
        for (var i = binary.length - 1; i >= 0; --i) {
            var b = binary[i];
            if (b == 1) {
                dec += Math.pow(2, p);
            }
            ++p;
        }
        return dec;
    };

    /**
     *unicode convert to utf-8
     */
    var _toUTF8Binary = function(c, binaryArray) {
        var mustLen = (8 - (c + 1)) + ((c - 1) * 6);
        var fatLen = binaryArray.length;
        var diff = mustLen - fatLen;
        while (--diff >= 0) {
            binaryArray.unshift(0);
        }
        var binary = [];
        var _c = c;
        while (--_c >= 0) {
            binary.push(1);
        }
        binary.push(0);
        var i = 0,
            len = 8 - (c + 1);
        for (; i < len; ++i) {
            binary.push(binaryArray[i]);
        }

        for (var j = 0; j < c - 1; ++j) {
            binary.push(1);
            binary.push(0);
            var sum = 6;
            while (--sum >= 0) {
                binary.push(binaryArray[i++]);
            }
        }
        return binary;
    };

    var __BASE64 = {
        /**
         *BASE64 Encode
         */
        encoder: function(str) {
            var base64_Index = [];
            var binaryArray = [];
            for (var i = 0, len = str.length; i < len; ++i) {
                var unicode = str.charCodeAt(i);
                var _tmpBinary = _toBinary(unicode);
                if (unicode < 0x80) {
                    var _tmpdiff = 8 - _tmpBinary.length;
                    while (--_tmpdiff >= 0) {
                        _tmpBinary.unshift(0);
                    }
                    binaryArray = binaryArray.concat(_tmpBinary);
                } else if (unicode >= 0x80 && unicode <= 0x7FF) {
                    binaryArray = binaryArray.concat(_toUTF8Binary(2, _tmpBinary));
                } else if (unicode >= 0x800 && unicode <= 0xFFFF) { //UTF-8 3byte
                    binaryArray = binaryArray.concat(_toUTF8Binary(3, _tmpBinary));
                } else if (unicode >= 0x10000 && unicode <= 0x1FFFFF) { //UTF-8 4byte
                    binaryArray = binaryArray.concat(_toUTF8Binary(4, _tmpBinary));
                } else if (unicode >= 0x200000 && unicode <= 0x3FFFFFF) { //UTF-8 5byte
                    binaryArray = binaryArray.concat(_toUTF8Binary(5, _tmpBinary));
                } else if (unicode >= 4000000 && unicode <= 0x7FFFFFFF) { //UTF-8 6byte
                    binaryArray = binaryArray.concat(_toUTF8Binary(6, _tmpBinary));
                }
            }

            var extra_Zero_Count = 0;
            for (var i = 0, len = binaryArray.length; i < len; i += 6) {
                var diff = (i + 6) - len;
                if (diff == 2) {
                    extra_Zero_Count = 2;
                } else if (diff == 4) {
                    extra_Zero_Count = 4;
                }
                //if(extra_Zero_Count > 0){
                //	len += extra_Zero_Count+1;
                //}
                var _tmpExtra_Zero_Count = extra_Zero_Count;
                while (--_tmpExtra_Zero_Count >= 0) {
                    binaryArray.push(0);
                }
                base64_Index.push(_toDecimal(binaryArray.slice(i, i + 6)));
            }

            var base64 = '';
            for (var i = 0, len = base64_Index.length; i < len; ++i) {
                base64 += BASE64_MAPPING[base64_Index[i]];
            }

            for (var i = 0, len = extra_Zero_Count / 2; i < len; ++i) {
                base64 += '=';
            }
            return base64;
        },
        /**
         *BASE64  Decode for UTF-8 
         */
        decoder: function(_base64Str) {
            var _len = _base64Str.length;
            var extra_Zero_Count = 0;
            /**
             *计算在进行BASE64编码的时候，补了几个0
             */
            if (_base64Str.charAt(_len - 1) == '=') {
                //alert(_base64Str.charAt(_len-1));
                //alert(_base64Str.charAt(_len-2));
                if (_base64Str.charAt(_len - 2) == '=') { //两个等号说明补了4个0
                    extra_Zero_Count = 4;
                    _base64Str = _base64Str.substring(0, _len - 2);
                } else { //一个等号说明补了2个0
                    extra_Zero_Count = 2;
                    _base64Str = _base64Str.substring(0, _len - 1);
                }
            }

            var binaryArray = [];
            for (var i = 0, len = _base64Str.length; i < len; ++i) {
                var c = _base64Str.charAt(i);
                for (var j = 0, size = BASE64_MAPPING.length; j < size; ++j) {
                    if (c == BASE64_MAPPING[j]) {
                        var _tmp = _toBinary(j);
                        /*不足6位的补0*/
                        var _tmpLen = _tmp.length;
                        if (6 - _tmpLen > 0) {
                            for (var k = 6 - _tmpLen; k > 0; --k) {
                                _tmp.unshift(0);
                            }
                        }
                        binaryArray = binaryArray.concat(_tmp);
                        break;
                    }
                }
            }

            if (extra_Zero_Count > 0) {
                binaryArray = binaryArray.slice(0, binaryArray.length - extra_Zero_Count);
            }

            var unicode = [];
            var unicodeBinary = [];
            for (var i = 0, len = binaryArray.length; i < len;) {
                if (binaryArray[i] == 0) {
                    unicode = unicode.concat(_toDecimal(binaryArray.slice(i, i + 8)));
                    i += 8;
                } else {
                    var sum = 0;
                    while (i < len) {
                        if (binaryArray[i] == 1) {
                            ++sum;
                        } else {
                            break;
                        }
                        ++i;
                    }
                    unicodeBinary = unicodeBinary.concat(binaryArray.slice(i + 1, i + 8 - sum));
                    i += 8 - sum;
                    while (sum > 1) {
                        unicodeBinary = unicodeBinary.concat(binaryArray.slice(i + 2, i + 8));
                        i += 8;
                        --sum;
                    }
                    unicode = unicode.concat(_toDecimal(unicodeBinary));
                    unicodeBinary = [];
                }
            }
            return unicode;
        }
    };

    window.BASE64 = __BASE64;
})();
var token = '123456789';
var token2 = '123456789';

var iiiid = '123456789';

function t1($port) {
    yAjax('http://kj.com/index/token', '', function($data) {
        ywebsock('192.168.6.69', $port, 'login', 'run', { "uid": $data.uid });

        iiiid = $data.uid;
        token2 = $data.token;

        d(iiiid);
    });


    /*socksend('login','',{"uid":1000526});*/
}

function t2($touser, $msg) {
    //获取chatid
    token = token2;
    $url = "http://kj.com/index/createchatid?fid=" + iiiid + "&tid=" + $touser;
    yAjax($url, '', function($data) {
        socksend('cheat', '', { "chatid": $data.chatid, 'msg': $msg });
        /*token2=$data.token;*/

    });

}
/*****************wsock*******/
function sockdata($action, $fun, $data) {
    if (typeof($data) !== 'object') {
        $data = {};
    } else {
        $data.cookie = getCookie('XXOOuserinfo');
    }

    $data = JSON.stringify($data);
    //	$data=BASE64.encoder($data);	
    $data = encrypts($data, token);
    $content = { 'data': $data, 'stype': 1, 'action': $action, 'fun': $fun, 'tid': Math.ceil(Math.random() * 10) };
    $content = JSON.stringify($content);
    return $content;

};

function socksend($action, $fun, $data) {

    $da = sockdata($action, $fun, $data);
    /*console.log($da);*/
    ws.send($da);
};

function getCookie(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");

    if (arr = document.cookie.match(reg))

        return (arr[2]);
    else
        return null;
}

function setCookie(name, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + "; path=/";
}

function delCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null)
        document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}

function gettime() {
    var timestamp = Date.parse(new Date()) / 1000;
    timestamp = Math.round(timestamp);
    return timestamp;
}
//获取本地缓存
function getStorage($name) {
    if (window.localStorage) {
        $data = localStorage.getItem($name);
        $data = jta($data);
        if (!$data) return false;
        $time = gettime();
        $reptime = $data['time'];
        if ($time > $reptime && $reptime != 0) {
            localStorage.removeItem($name);
            return false;
        }
        return $data['data'];
        // alert('This browser supports localStorage');
    } else {
        return false;
        // alert('This browser does NOT support');
    }

}
/**
 * 保存本地缓存
 * @param {string} $name  键名
 * @param {object} $val 	键值
 * @param {int} $exptime 	有效时间/秒
 */
function setStorage($name, $val, $exptime) {
    if (window.localStorage) {

        if (!$exptime) {
            $time = 0;
        } else {
            $tie = gettime();
            $time = $tie + $exptime;
        }
        $data = { 'data': $val, 'time': $time };
        $data = atj($data);
        $data = localStorage.setItem($name, $data);
        return $data;
        // $data = jta($data);
        // if (!$data) return false;
        // $time = gettime();
        // $reptime = $data['time'];
        // if ($time > $reptime) {
        // 	localstorage.removeItem($name);
        // 	return false;
        // }
        // return $data['data'];
        // alert('This browser supports localStorage');
    } else {
        return false;
        // alert('This browser does NOT support');
    }

}
var yip, ypost, yaction, ydata, tcallback, ws;

function reconnect() {
    d('心跳检测');
    if (ws.readyState != 1) {
        d('尝试重连');
        ywebsock(yip, yport, yaction, ydata, ycallback);
    }

}
var heartCheck = {
    timeout: 60000, //60ms
    timeoutObj: null,
    reset: function() {
        clearTimeout(this.timeoutObj);
        this.start();
    },
    start: function() {
        this.timeoutObj = setTimeout(function() {
            /*showd('心跳了');*/
            reconnect();

        }, this.timeout)
    }
}
var decode = function($data) {
    d(unescape($data['data']));
    if ($data['data']) {
        $decode = decrypts($data['data'], token);
        $data['data'] = jta($decode);
    }
    d($data);
}

function ywebsock($ip, $port, $action, $fun, $data, $callback) {
    yip = $ip;
    yport = $port;
    yaction = $action;
    ydata = $data;
    ycallback = $callback;
    if (typeof(ycallback) != 'function') {
        ycallback = decode;
    }
    try {
        $url = 'ws://' + $ip + ":" + $port + '';
        //		$url='wss://'+$ip+":"+$port+'';

        ws = new WebSocket($url);
        //每隔一分钟检测重连
        /*heartCheck.start();*/

        //握手监听函数
        ws.onopen = function() {
                //状态为1证明握手成功，然后把client自定义的名字发送过去

                if (ws.readyState == 1) {
                    //握手成功后对服务器发送信息
                    /*$da=sockdata($action,$data);
				
                    ws.send($da);*/
                    socksend($action, '', $data);
                } else {
                    heartCheck.start();
                }
            }
            //错误返回信息函数
        ws.onerror = function(e) {
            d(e);
            /*heartCheck.start();*/
            /*console.log("error");*/
        };
        //监听服务器端推送的消息
        ws.onmessage = function(msg) {
                ////
                /*d(msg.data);
                d(msg);*/
                try {

                    data2 = jta(msg.data);
                    ycallback(data2);
                } catch (e) {
                    d(e);
                }

            }
            //断开WebSocket连接
        ws.onclose = function() {

            ws = false;
            heartCheck.start();

        }
    } catch (e) {
        d(e);
        d('浏览器不支持在线聊天;请更换浏览器');
    }
}


function encrypts(message, key) {
    var keyHex = CryptoJS.enc.Utf8.parse(key);
    var encrypted = CryptoJS.DES.encrypt(message, keyHex, {
        mode: CryptoJS.mode.ECB,
        padding: CryptoJS.pad.Pkcs7
    });
    return encrypted.toString();
}

function decrypts(ciphertext, key) {
    var keyHex = CryptoJS.enc.Utf8.parse(key);
    // direct decrypt ciphertext
    var decrypted = CryptoJS.DES.decrypt({
        ciphertext: CryptoJS.enc.Base64.parse(ciphertext)
    }, keyHex, {
        mode: CryptoJS.mode.ECB,
        padding: CryptoJS.pad.Pkcs7
    });
    //d(CryptoJS.enc.Utf8);
    return decrypted.toString(CryptoJS.enc.Utf8);
}