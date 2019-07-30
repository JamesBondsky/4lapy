$(function(){

    $form = $('.form-fan-register');
    defaultErrorText = "Упс, что-то пошло не так. Пожалуйста, сообщите нам об этой проблеме!";

    $form.on('submit', function(e){
       e.preventDefault();
       return false;
    });

    $form.find('.js-submit-form').on('click', function(){
        if($form.find('[name="check_number"]').val() === ''){
            $form.find('.response-messsage').html('Введите промокод');
            return false;
        }

        $form.find('.response-messsage').html('<span style="color: #0f6198">Отправляем...</span>');

        $.ajax({
            type: "POST",
            url: '/dobrolap/',
            data: $form.serialize(),
            success: function(data) {
                if(data.success !== undefined) {
                    //--замена содержимого блока регистрации на благодарность по нажатию кнопки
                    $('#fanreg .row').addClass('justify-content-center');
                    $('#fanreg .row').html('<div class="col-md-12"><h2 class="">Спасибо за регистрацию ФАНА!</h2><h5 class="mb-4">ваши данные отправлены</h5><hr /></div>');
                }
                else if(data.error !== undefined){
                    $form.find('.response-messsage').html(data.error);
                } else {
                    $form.find('.response-messsage').html(defaultErrorText);
                }
            },
            error: function(){
                $form.find('.response-messsage').html(defaultErrorText);
            },
            dataType: 'json'
        });
    });

    var setScrollCookie = function()
    {
        $.cookie('dobrolap_scroll_form', 1, { path: '/', expires: 365 });
        console.log('dobrolap_scroll_form', 1);
    };
    var clearScrollCookie = function()
    {
        $.cookie('dobrolap_scroll_form', 0, { path: '/', expires: 365 });
        console.log('dobrolap_scroll_form', 0);
    };

    console.log('dobrolap_scroll_form initial', $.cookie('dobrolap_scroll_form'));
    if($.cookie('dobrolap_scroll_form') === 1){
        $('html, body').animate({
            scrollTop: $('.js-scroll-to').offset().top - 70
        }, 500, function() {
            clearScrollCookie();
        });
    }

    $('#thanks .js-open-popup').on('click', function(){
        setScrollCookie();
    });

    $(document).on('click','.js-close-popup', clearScrollCookie);
    $(document).on('click','.js-open-popup.js-toggle-popover-mobile-header', clearScrollCookie);
    $(document).on('click','.js-close-popup', function () {
        if (event.target === this) {
            clearScrollCookie();
        }
    });

    $

});