$('body').on('change keydown keyup', 'form.js-referal-form input#number-card', function () {
    var val = $(this).val().replace(/\D/g, '');
    if ($(this).hasClass('ok') && val.length === 13) {
        $.ajax({
                   url:      $(this).data('url'),
                   data:     {'card': val},
                   type:     $(this).data('method'),
                   dataType: "json"
               }).done(function (result) {
            if (result.success && !!result.data) {
                var $form = $('form.js-referal-form');
                var data = result.data;
                if (data.length > 0 && !!data.card) {
                    var card = result.data.card;
                    if (card.length > 0) {
                        $form.find('input#last-name').val(card.last_name);
                        $form.find('input#first-name').val(card.name);
                        $form.find('input#patronymic').val(card.second_name);
                        $form.find('input#phone-referal').val(card.phone);
                        $form.find('input#email-referal').val(card.email);
                    }
                }
            }
        });
    }
});