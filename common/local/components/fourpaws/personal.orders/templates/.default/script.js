$(document).ready(function () {
    var getOrders = function (button) {
        var _self = button,
            url = button.data('url'),
            page = button.data('page');
        $('body').trigger('start-preloader');

        $.ajax({
            url: url + '?page=' + page,
            dataType: 'json',
            // xhrFields: {
            //     withCredentials: true
            // },
            success: function (data) {
                $html = $(data).find('.b-container--catalog-filter');
                $('#personal-order-list').html(data.data.html);
                $('body').trigger('update');
                $(window).trigger('resize');
                $('body').trigger({
                    type: 'reload-redirect',
                    json: data,
                });
                $('.b-preloader').removeClass('active');
                $('body').trigger('stop-preloader');

                $('html, body').animate({scrollTop: 0}, 500);
            },
            beforeSend: function () {
                $('.b-preloader').addClass('active');
            },
            complete: function () {
                $('body').trigger('stop-preloader');
            },
            error: function () {
                $('body').trigger('stop-preloader');
                $('.b-preloader').removeClass('active');
            }
        });
    };
    $('body').on('click', '.js-pagination-personal-order-list', function (e) {
        e.preventDefault();
        $('.js-pagination').removeClass('active');
        $(this).addClass('active');
        getOrders($(this));
    });
    // $('.js-orders-more').on('click', function (e) {
    //     var _self = button,
    //         url = button.data('url'),
    //         page = button.data('page');
    //     e.preventDefault();
    //     $.ajax({
    //         url: url + '?page=' + page,
    //         type: 'GET',
    //         success: function (data) {
    //             if (!data.success) {
    //                 return;
    //             }
    //             $('#personal-order-list').html(data.data.html);
    //         },
    //         beforeSend: function () {
    //             $('.b-preloader').addClass('active');
    //         },
    //         complete: function () {
    //             $('.b-preloader').removeClass('active');
    //         }
    //     })
    // });

    // отмена заказа
    var orderCancelPopup = $('.js-popup-section[data-popup="cancel-order"]');
    var orderItem = undefined;

    $('.js-cancel-order-popup').click(function () {
        alertPopup();
        orderItem = $(this);
    });

    $('.js-cancel-order').click(function () {
        var data = {
            'orderId': orderItem.attr('data-order-id'),
        };

        $.ajax({
            url: '/ajax/sale/order/cancel/',
            data: data,
            type: 'post',
            dataType: 'json',
            success: function (json) {
                data = json.data;

                orderCancelPopup.find('.js-info').css('display', 'none')
                orderCancelPopup.find('.js-result').css('display', 'flex')

                var msg = '';

                if (json.success) {
                    msg = json.message;

                    orderItem.find('.js-link-text').text('Отменен');

                    orderItem.unbind();
                } else {
                    msg = data.errors[0];
                }

                orderCancelPopup.find('.js-result').find('.js-result-text').text(msg);

                $('.b-preloader').removeClass('active');

                setTimeout(function () {
                    $('.js-popup-section.opened').find('.js-close-popup').trigger('click');
                }, 2000);
            },
            beforeSend: function () {
                $('.b-preloader').addClass('active');
            }
        });
    });

    function alertPopup() {
        $('.js-popup-wrapper').addClass('active');
        orderCancelPopup.find('.js-info').css('display', 'flex')
        orderCancelPopup.find('.js-result').css('display', 'none')
        orderCancelPopup.find('.js-result').find('.js-result-text').empty()
        orderCancelPopup.addClass('opened').show();
    };
});
