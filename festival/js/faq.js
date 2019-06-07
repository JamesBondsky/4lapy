$(document).ready(function($) {

    $('[data-faq-answer]').on('show.bs.collapse', function (event) {
        $(this).closest('[data-faq-list]').find('.collapse.in').collapse('hide');
        $(this).closest('[data-faq-item]').addClass('open');
    })

    $('[data-faq-answer]').on('hide.bs.collapse', function (event) {
        $(this).closest('[data-faq-item]').removeClass('open');
    })

    $('[data-faq-slider]').slick({
        dots: true,
        arrows: false,
        infinite: true,
        slidesToShow: 2,
        slidesToScroll: 2,
        adaptiveHeight: true,
        responsive: [
            {
                breakpoint: 620,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    })

})