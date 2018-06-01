$(function(){
   $(document).on('change', '.b-input-line--file input[type=file]', function(){
       console.log('11');
       var file_name = this.value.replace(/\\/g, '/').replace(/.*\//, '');
       var $fileNameText = $(this).closest('.b-input-line--file').find('.fileNameText');
       if(file_name.length > 0) {
           $fileNameText.text('Выбранный файл: '+file_name).css('display', 'block');
       } else {
           $fileNameText.text('').css('display', 'none');
       }
   });
});