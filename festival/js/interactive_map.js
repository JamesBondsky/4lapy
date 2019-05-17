$(document).ready(function(){

    $('[data-interactive-map-current]').on('click', function(event){
        event.preventDefault();
        $('[data-interactive-map]').addClass('is_open_' + $(this).data('interactive-map-current'));
    })

    $('[data-interactive-map-back]').on('click', function(event){
        event.preventDefault();
        $('[data-interactive-map]').removeClass('is_open_pavilion');
    })


    $('[data-interactive-map-pavilion-closecontent]').on('click', function(event){
         event.preventDefault();

        var selected = $(this).data('interactive-map-pavilion-point'),
            title = $(this).data('point-title'),
            description = $(this).data('point-description');

        $('[data-interactive-map-pavilion-title]').html(title);
        $('[data-interactive-map-pavilion-description]').html(description);
        //$('[data-interactive-map-pavilion-image]').removeAttr('style');

        $('[data-interactive-map-pavilion-brand]').removeClass('selected');
        $('[data-interactive-map-pavilion]').removeClass('is_open_brands');

        $('[data-interactive-map-pavilion-point]').removeClass('active');
    })

    $('[data-interactive-map-pavilion-point]').on('click', function(event){
        event.preventDefault();

        var selected = $(this).data('interactive-map-pavilion-point'),
            title = $(this).data('point-title'),
            description = $(this).data('point-description'),
            image = $(this).data('point-image');

        $('[data-interactive-map-pavilion-title]').html(title);
        $('[data-interactive-map-pavilion-description]').html(description);

        $('[data-interactive-map-pavilion-point]').removeClass('active');
        $(this).addClass('active');

        $('[data-interactive-map-pavilion-brands]').removeClass('hidden');
        $('[data-interactive-map-pavilion]').addClass('is_open_brands');

        $('[data-interactive-map-pavilion-brand]').removeClass('selected');
        $('[data-interactive-map-pavilion-brand="' + selected + '"]').addClass('selected');

        /*$('[data-interactive-map-pavilion-image]').removeAttr('style');
        $('[data-interactive-map-pavilion-image]').removeClass('set_image');

        if (!!image && image.length){
            $('[data-interactive-map-pavilion-image]').addClass('set_image');
            $('[data-interactive-map-pavilion-image]').css('background-image', 'url(' + image + ')');
        }*/

    })

});