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
});
