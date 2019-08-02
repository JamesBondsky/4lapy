$(function(){
   $('.js-open-popup[data-popup-id="shelter_popup"]').on('click', function(){
      contentId = $(this).data('content-id');
      html = $('.js-popup-content[data-id="'+contentId+'"]').html();
      $('[data-popup="shelter_popup"]').html(html);
   });
});