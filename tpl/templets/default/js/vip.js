// var new_fiction_Swiper = new Swiper('.new_fiction', {
//       slidesPerView: 3,
//       spaceBetween: 30,
//       pagination: {
//         el: '.new_fiction .swiper-pagination',
//         clickable: true,
//       },
//     });

// var new_cartoon_Swiper = new Swiper('.new_cartoon', {
//       slidesPerView: 3,
//       spaceBetween: 30,
//       pagination: {
//         el: '.new_cartoon .swiper-pagination',
//         clickable: true,
//       },
//     });

check_body_size();
$('.vip').on('click', function() {

    go_download();
});


$('.open_button_page').on('click', function() {
  go_download();

});

$('.vip_open_list li').on('click', function() {
    if ($(this).hasClass('isOn')) {

    } else {
        $(this).siblings().removeClass('isOn');
        $(this).addClass('isOn');
    }
});