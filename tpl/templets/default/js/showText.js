var st_timer = null;
$(function() {
    showInit();
});


function showInit() {
    //透明黑色背景
    checkShowInit = true;
    $("<div id='st_mask' onclick='closeMask()'></div>").appendTo("body").css({
        'width': '100%',
        'height': '100%',
        'background': 'rgba(0,0,0,.4)',
        'position': 'fixed',
        'left': '0',
        'top': '0',
        'display': 'none',
        'z-index': '1'
    });
    //--------------------------------在body最后添加Confirm的节点
    $("<div id='st_confirmBox'></div>").appendTo("body").css({
        'width': '100%',
        'position': 'fixed',
        'left': '0',
        'top': '34%',
        'text-align': 'center',
        'display': 'none',
        'z-index': '2',
    });
    $("<div id='st_confirm'></div>").appendTo("#st_confirmBox").css({
        'width': '80%',
        'margin': '0 auto',
        'background': '#fff',
        'border-radius': '3px',
        'overflow': 'hidden',
        'padding-top': '20px',
        'text-align': 'center',

    });
    $("<span id='st_confirm_text'></span>").appendTo("#st_confirm").css({
        'background': '#fff',
        'overflow': 'hidden',
        'padding-top': '20px',
        'text-align': 'center',
        'display': 'block',
        'padding': '15px 8px 30px',

    });
    $("<span class='st_confirm_btn cancel'></span>").appendTo("#st_confirm").css({
        'background': '#fff',
        'color': '#8d8d8d',
        'padding': '8px',
        'text-align': 'center',
        'display': 'block',
        'width': '50%',
        'margin': '0 auto',
        'float': 'left',
        'box-sizing': 'border-box',
        'border-top': '1px solid #cfcfcf',
        'overflow': 'hidden',
        'text-overflow': 'ellipsis',
        'white-space': 'nowrap'
    });
    $("<span class='st_confirm_btn success'></span>").appendTo("#st_confirm").css({
        'background': '#1b79f8',
        'color': '#fff',
        'padding': '8px',
        'text-align': 'center',
        'display': 'block',
        'width': '50%',
        'margin': '0 auto',
        'float': 'left',
        'box-sizing': 'border-box',
        'border-top': '1px solid #1b79f8',
        'overflow': 'hidden',
        'text-overflow': 'ellipsis',
        'white-space': 'nowrap'
    });
    $("<div></div>").appendTo("#st_confirm").css({
        'clear': 'both',
        'display': 'block',
    });

    //--------------------------------在body最后添加Alert节点
    $("<div id='st_alertBox'></div>").appendTo("body").css({
        'width': '100%',
        'position': 'fixed',
        'left': '0',
        'top': '34%',
        'text-align': 'center',
        'display': 'none',
        'z-index': '2',
    });
    $("<div id='st_alert'></div>").appendTo("#st_alertBox").css({
        'width': '80%',
        'margin': '0 auto',
        'background': '#fff',
        'border-radius': '2px',
        'overflow': 'hidden',
        'padding-top': '20px',
        'text-align': 'center',
    });
    $("<span id='st_alert_text'></span>").appendTo("#st_alert").css({
        'background': '#fff',
        'overflow': 'hidden',
        'padding-top': '20px',
        'text-align': 'center',
        'display': 'block',
        'padding': '15px 8px 30px',
    });
    $("<span id='st_alert_btn' onclick='closeMask()'></span>").appendTo("#st_alert").css({
        'background': '#1b79f8',
        'color': '#fff',
        'padding': '8px',
        'text-align': 'center',
        'display': 'block',
        'width': '72%',
        'margin': '0 auto',
        'margin-bottom': '20px',
        'border-radius': '2px',
        'overflow': 'hidden',
        'text-overflow': 'ellipsis',
        'white-space': 'nowrap'
    });

    //---------------------------------在body最后添加Toast节点
    $("<div id='st_toastBox'></div>").appendTo("body").css({
        'width': '100%',
        'position': 'fixed',
        'left': '0',
        'top': '45%',
        'text-align': 'center',
        'display': 'none'
    });

    $("<div id='st_toastContent'></div>").appendTo("#st_toastBox").css({
        'color': '#fff',
        'background': 'rgba(0,0,0,.8)',
        'padding': '12px 12px',
        'border-radius': '4px',
        'max-width': '80%',
        'display': 'inline-block'
    });
    $("<img src='' id='st_toastImg'/>").appendTo("#st_toastContent").css({
        'width': '20px',
        'height': '20px',
        'margin-bottom': '10px',
        'display': 'none'
    });
    $("<p id='st_toastText'></p>").appendTo("#st_toastContent").css({
        'color': '#fff',
    });

}

function showToast(obj) {
    if (!obj.text) {
        return false;
    }
    clearTimeout(st_timer);
    $('#st_toastBox').hide();
    $('#st_toastImg').hide();
    var text = obj.text;
    var time = parseInt(obj.time ? obj.time : 2300);
    var speed = obj.speed ? obj.speed : 'normal';
    var top = obj.top ? obj.top : '45%';
    var img = obj.img ? obj.img : '';
    if (obj.zindex) {
        var zindex = parseInt(obj.zindex);
        $('#st_mask').css({ 'z-index': zindex - 1 });
        $('#st_toastBox').css({ 'z-index': zindex });
    } else {
        $('#st_mask').css({ 'z-index': 1 });
        $('#st_toastBox').css({ 'z-index': 2 });
    }
    if (img) {
        $('#st_toastImg').attr("src", "images/showText/" + img + ".png");
        $('#st_toastImg').show();
    }

    $('#st_toastBox').css({ 'top': top });

    $('#st_toastText').text(text);
    $('#st_toastBox').fadeIn(speed);
    st_timer = setTimeout(function() {
        $('#st_toastBox').fadeOut();
    }, time);

}

function showAlert(obj) {

    if (!obj.text) {
        return false;
    } else {
        var text = obj.text;
        var bgColor = obj.bgColor ? obj.bgColor : '#1b79f8';
        var color = obj.color ? obj.color : '#fff';
        var btnText = obj.btnText ? obj.btnText : '确定';
        var top = obj.top ? obj.top : '34%';

        if (obj.zindex) {
            var zindex = parseInt(obj.zindex);
            $('#st_mask').css({ 'z-index': zindex - 1 });
            $('#st_alertBox').css({ 'z-index': zindex });
        } else {
            $('#st_mask').css({ 'z-index': 1 });
            $('#st_alertBox').css({ 'z-index': 2 });
        }

        $('#st_alert_text').text(text);
        $('#st_alert_btn').css({ 'background': bgColor });
        $('#st_alert_btn').css({ 'color': color });
        $('#st_alert_btn').text(btnText);
        $('#st_alertBox').css({ 'top': top });
        $('#st_mask,#st_alertBox').show();

        if (obj.success) {
            $('#st_alert_btn').off('click').on('click', function() {
                obj.success();
            });
        }
    }

}

function showConfirm(obj) {
    if (!obj.text) {
        return false;
    }
    var text = obj.text;
    var rightText = obj.rightText ? obj.rightText : '确定';
    var rightBgColor = obj.rightBgColor ? obj.rightBgColor : '#1b79f8';
    var rightColor = obj.rightColor ? obj.rightColor : '#fff';

    var leftText = obj.leftText ? obj.leftText : '取消';
    var top = obj.top ? obj.top : '34%';
    if (obj.zindex) {
        var zindex = parseInt(obj.zindex);
        $('#st_mask').css({ 'z-index': zindex - 1 });
        $('#st_confirmBox').css({ 'z-index': zindex });
    } else {
        $('#st_mask').css({ 'z-index': 1 });
        $('#st_confirmBox').css({ 'z-index': 2 });
    }

    $('#st_confirm_text').text(text);
    $('.st_confirm_btn.cancel').text(leftText);
    $('.st_confirm_btn.success').text(rightText);
    $('.st_confirm_btn.success').css({
        'background': rightBgColor,
        'color': rightColor,
        'border-top': '1px solid ' + rightBgColor,
    });
    $('#st_confirmBox').css({ 'top': top });
    $('#st_mask,#st_confirmBox').show();

    if (obj.cancel) {
        $('.st_confirm_btn.cancel').off('click').on('click', function() {
            closeMask();
            obj.cancel();
        })
    } else {
        $('.st_confirm_btn.cancel').off('click').on('click', function() {
            closeMask();
        });
    }
    if (obj.success) {
        $('.st_confirm_btn.success').off('click').on('click', function() {
            closeMask();
            obj.success();
        })
    } else {
        $('.st_confirm_btn.success').off('click').on('click', function() {
            closeMask();
        });
    }
}

function closeMask() {
    $('#st_mask,#st_alertBox,#st_confirmBox').hide();
}



// //Toast的使用方法
// showToast({
//     text: '点击已生效，这是showToast', //【必填】，否则不能正常显示 , 剩余的其他不是必填
//     bottom: '10%', //toast距离页面底部的距离
//     zindex: 2, //为了防止被其他控件遮盖，z-index默认为2
//     speed: 500, //toast的显示速度
//     time: 5000 //toast显示多久以后消失
// });
// //Alert的使用方法：
// showAlert({
//     text: 'showAlert,点击按钮调用回调函数', //【必填】，否则不能正常显示
//     btnText: '确定', //按钮的文本
//     top: '34%', //alert弹出框距离页面顶部的距离
//     zindex: 5, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1，修改后黑色遮盖层的z-index是这个数值的-1
//     color: '#fff', //按钮的文本颜色，默认白色
//     bgColor: '#1b79f8', //按钮的背景颜色，默认为#1b79f8
//     success: function() { //点击按钮后的回调函数
//         showToast({
//             text: '调用了回调函数！',
//         })
//     }
// });
// //Confirm的使用方法：
// showConfirm({
//     text: 'This is Confirm', //【必填】，否则不能正常显示
//     rightText: '成功', //右边按钮的文本
//     rightBgColor: '#1b79f8', //右边按钮的背景颜色，【不能设置为白色背景】
//     rightColor: '#fff', //右边按钮的文本颜色，默认白色
//     leftText: '过于长的文本过于长的文本', //左边按钮的文本
//     top: '34%', //弹出框距离页面顶部的距离
//     zindex: 5, //为了防止被其他控件遮盖，默认为2，背景的黑色遮盖层为1,修改后黑色遮盖层的z-index是这个数值的-1
//     success: function() { //右边按钮的回调函数
//         showToast({
//             text: '调用了成功的回调函数！'
//         })
//     },
//     cancel: function() { //左边按钮的回调函数
//         showToast({
//             text: '调用了失败的回调函数！'
//         })
//     }
// });