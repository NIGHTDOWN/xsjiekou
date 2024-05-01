check_body_size();
// 设置加载几个AJAX
var load_number = 0;

function check_load() {
    load_number--;
    if (load_number <= 0) {
        setTimeout(function() {
            load_end();
        }, 500)

    }
}

initialization();
// 初始化
function initialization() {
    check_load();
}

$(".fqa_lists_title").click(function() {
    var fqa_lists = $(this).parent('.fqa_lists');
    // console.log(fqa_lists);
    fqa_lists.find('ul').slideToggle(300);

    if (fqa_lists.hasClass('on')) {
        fqa_lists.removeClass('on');
        fqa_lists.find('img').css({ 'transform': 'rotate(-90deg)' });
    } else {
        fqa_lists.addClass('on');
        fqa_lists.find('img').css({ 'transform': 'rotate(0deg)' });
    }
});