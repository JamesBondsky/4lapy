$(document).ready(function () {
    var $button = $('.js-orders-more');
    $button.on('click', function(e) {
        e.preventDefault();
        $button.prop('disabled', true);

        $.ajax({
            url: $button.attr('data-url') + '?page=' + $button.attr('data-page'),
            type: 'GET',
            success: function (data) {
                if (!data.success) {
                    return;
                }

                $button.attr('data-page', parseInt($button.attr('data-page')) + 1);
                if (data.data.count < 10) {
                    $button.hide();
                }
                $button.prop('disabled', false);
                $('.b-account__accordion-order-list').append(data.data.html);
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
