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

        $('[data-point-image-left]').html('');
        $('[data-point-image-right]').html('');
    })

    $('[data-interactive-map-pavilion-point]').on('click', function(event){
        event.preventDefault();

        var selected = $(this).data('interactive-map-pavilion-point'),
            title = $(this).data('point-title'),
            description = $(this).data('point-description'),
            image = $(this).data('point-image'),
            images = $(this).data('point-images');

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

        if (!!images){
            var arrImages = Object.keys(images).map(function(key) {
              return images[key];
            });
            var htmlLeft = '',
                htmlRight = '';

            for (var i = 0; i < arrImages.length/2; i++) {
                if (arrImages[i].length){
                    htmlLeft = htmlLeft + '<div class="interactive_map_pavilion__image-item"><div class="image" style="background-image: url(' + arrImages[i] + ')"></div></div>';
                }
            }
            for (var i = arrImages.length/2; i < arrImages.length; i++) {
                if (arrImages[i].length){
                    htmlRight = htmlRight + '<div class="interactive_map_pavilion__image-item"><div class="image" style="background-image: url(' + arrImages[i] + ')"></div></div>';
                }
            }

            $('[data-point-image-left]').html(htmlLeft);
            $('[data-point-image-right]').html(htmlRight);
        }



    })


    $('[data-interactive-map-current="pavilion"]').on('mouseenter', function(){
        $('[data-interactive-map]').addClass('hover_pavilion');
    })

    $('[data-interactive-map-current="pavilion"]').on('mouseleave', function(){
        $('[data-interactive-map]').removeClass('hover_pavilion');
    })

});