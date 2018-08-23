$(function() {
    $(document).on('click', 'a', function(){
        var show = $(this).data('show');
        if(show === 'bx-auth') {
            $('div.bx-auth').show();
            $('div.js-forgot').hide();
        } else if(show === 'js-forgot') {
            $('div.bx-auth').hide();
            $('div.js-forgot').show();
        }
    });
});