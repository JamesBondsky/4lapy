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
                    var monthNames = ["января", "февраля", "марта", "апреля", "мая", "июня",
                        "июля", "августа", "сентября", "октября", "ноября", "декабря"
                    ];
                    var resultDate = new Date();
                    var daysToAdd = (8 - resultDate.getDay()) % 7;
                    if (daysToAdd == 0) {
                        daysToAdd = 7;
                    }
                    resultDate.setDate(resultDate.getDate() + daysToAdd);
                    if (resultDate < new Date('2019-08-12')) {
                        resultDate = new Date('2019-08-12');
                    }
                    var resultDateFormatted = resultDate.getDate() + ' ' + monthNames[resultDate.getMonth()];
                    $('#fanreg .row').html('<div class="col-md-12 heading-section text-center">' +
                            '<h2 class="">Спасибо, что присоединились<br />к команде ДОБРОЛАП!</h2>' +
                            '<hr />' +
                            '<h5 class="mb-4">' + resultDateFormatted + ' на электронную почту Вы получите индивидуальное предложение или станете победителем в розыгрыше одного из фан-бонусов:' +
                            '<ul>' +
                                '<li>Стать участником фотосессии</li>' +
                                '<li>Получить футболку с художественным изображением питомца</li>' +
                                '<li>Стать автором рубрики/поста в социальных сетях компании «Четыре лапы»</li>' +
                                '<li>Стать лицом рекламной кампании  «Твой питомец звезда».</h5></li>' +
                            '</ul>\n' +
                        '</div>');
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
    };
    var clearScrollCookie = function()
    {
        $.cookie('dobrolap_scroll_form', 0, { path: '/', expires: 365 });
    };

    if($.cookie('dobrolap_scroll_form') === '1'){
        $('html, body').animate({
            scrollTop: $('#fanreg').offset().top - 70
        }, 500, clearScrollCookie);
    }

    $('#thanks .js-open-popup[data-popup-id="authorization"]').on('click', setScrollCookie);

    $(document).on('click','.js-close-popup', clearScrollCookie);
    $(document).on('click','.js-open-popup.js-toggle-popover-mobile-header', clearScrollCookie);
    $(document).on('click','.js-close-popup', function () {
        if (event.target === this) {
            clearScrollCookie();
        }
    });

});