$('body').on('keydown', 'form.js-referal-form input#number-card', function () {
    var val = $(this).val().replace(/\D/g, '');
    if (val.length === 13) {
        $.ajax({
                   url:      $(this).data('url'),
                   data:     {'card': val},
                   type:     $(this).data('method'),
                   dataType: "json"
               }).done(function (result) {
            var $form = $('form.js-referal-form');
            if (result.success && !!result.data && result.data.length > 0) {
                $form.find('input#last-name').val(result.data.last_name);
                $form.find('input#first-name').val(result.data.name);
                $form.find('input#patronymic').val(result.data.second_name);
                $form.find('input#phone-referal').val(result.data.phone);
                $form.find('input#email-referal').val(result.data.email);
            }
        });
    }
});