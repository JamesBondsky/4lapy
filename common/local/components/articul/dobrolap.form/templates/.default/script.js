$(function(){

    $form = $('.form-fan-register');
    defaultErrorText = "Упс, что-то пошло не так. Пожалуйста, сообщите нам об этой проблеме!";

    $form.on('submit', function(e){
       e.preventDefault();
       return false;
    });

    $form.find('.js-submit-form').on('click', function(){
        $form.find('.response-messsage').html('');

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
});