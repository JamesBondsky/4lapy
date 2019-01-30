$(document).ready(function () {
    $('.js-orders-more').on('click', function (e) {
        var _self = $(this),
            url = $(this).data('url'),
            page = $(this).data('page');
        e.preventDefault();
        $.ajax({
            url: url + '?page=' + page,
            type: 'GET',
            success: function (data) {
                if (!data.success) {
                    return;
                }
                $('#personal-order-list').html(data.data.html);
            },
            beforeSend: function () {
                $('.b-preloader').addClass('active');
            },
            complete: function () {
                $('.b-preloader').removeClass('active');
            }
        })
    });
});
