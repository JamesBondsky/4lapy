$(window).load(function(){
  var unlock;

  // mask
  $('input[data-mask-phone]').mask('+7 (999) 999-99-99', {
    placeholder: "_",
    autoclear: false,
    completed: function(){
      //console.log(this.val());
      this.valid();
    }
  });

  $.validator.addMethod(
      'isPhone',
      function(value, element) {
          if(!!$(element).attr('required')){
              // поле обязательно для заполнения
              // ни одно из условий не должно выполняться
              return !((value.length == 0) || (value == '+7 (___) ___-__-__') || value.indexOf('_') > 0);
          } else {
              // поле необязательно для заполнения
              // если часть поля заполнили
              if ((value.length !== 0) || (value !== '+7 (___) ___-__-__')){
                  // то валидируем
                  return !(value.indexOf('_') > 0);
              } else {
                  // иначе пропускаем
                  return true;
              }
          }
      },
      'Пожалуйста, введите корректный номер телефона'
  );

  /* настройки валидации */
  var validateObj = {
      lang: 'ru',
      ignore: function (index, el) {
         var $el = $(el);
         // Default behavior
         return $el.is('.ignore, *:not([name]), :hidden:not(input[type="file"], [class~=selectized]), :hidden > .selectized, .selectize-control .selectize-input input');
      },
      errorPlacement: function(error,element){
          var type = element.attr('type'),
              is_select = $(element).closest('.select');

          if(type == 'checkbox') {
              element.closest('.checkbox').addClass('checkbox_error');
              element.closest('.form-group').append(error);
          } else if(type == 'radio') {
              element.closest('.radio-group').addClass('radio-group_error').append(error);
          } else if(is_select) {
              element.closest('.select').addClass('select_error');
              element.closest('.form-group').append(error);
          } else if(type == 'file') {
              element.closest('.file-upload').addClass('file-upload_error').append(error);
          } else {
              error.insertAfter(element); // default error placement.
              $(element).closest('.form-group').addClass('form-group_error');
          }
      },
      unhighlight: function(element, errorClass, validClass){
          var type = $(element).attr('type'),
              is_select = $(element).closest('.select');

          if(type == 'checkbox') {
              $(element).closest('.checkbox').removeClass('checkbox_error');
          } else if(type == 'radio') {
              $(element).closest('.radio-group').removeClass('radio-group_error');
          } else if(is_select) {
              $(element).closest('.select').removeClass('select_error');
          } else if(type == 'file') {
              $(element).closest('.file-upload').removeClass('file-upload_error');
          }
          $(element).removeClass(errorClass).addClass(validClass);
          $(element).closest('.form-group').removeClass('form-group_error');
      },
      success: function(label){
          label.remove();
      }/*,
      invalidHandler: function(e,validator) {
          //validator.errorList contains an array of objects, where each object has properties "element" and "message".  element is the actual HTML Input.
          for (var i=0;i<validator.errorList.length;i++){
              console.log(validator.errorList[i]);
          }

          //validator.errorMap is an object mapping input names -> error messages
          for (var i in validator.errorMap) {
              console.log(i, ":", validator.errorMap[i]);
          }
      }*/
  };

  var popupFormFestival = $('[data-popup="form-festival"]');
  var responsePopupFormFestival = $('[data-popup="response-form-festival"]');
  if(popupFormFestival.length) {
    var timeIdPopupFormFestival,
        $formFestival = popupFormFestival.find('form');



    $formFestival.validate(validateObj);

    $formFestival.on('submit', function(event){
      event.preventDefault();
      event.stopPropagation();

      var form = $(this);

      if (form.valid()){
        clearTimeout(timeIdPopupFormFestival);
        timeIdPopupFormFestival = setTimeout(function() {

          var msg = form.serialize();

          // Отправляем данные формы в аналитику
          ga('send', 'event', {
            eventCategory: 'fest_fillform',
            eventAction: 'submit',
            fieldsObject: {
              dataForm: msg
            }
          });

          $.ajax({
            type: 'POST',
            url: '/ajax/landing/festival/user/add/',
            data: msg,
            dataType: 'json',
            success: function(data) {
              var messagePopup =  $('[data-popup="response-form-festival"]');

              if(data.success == 1) {
                messagePopup.find('[data-popup-content]').html(data.message);
              }else {
                messagePopup.find('[data-popup-content]').html('<p>' + data.message + '</p>');
              }

              popupFormFestival.removeClass('opened').fadeOut(0);
              messagePopup.addClass('opened').fadeIn(150, function () {
                  unlock = locky.lockyOn('.js-popup-wrapper');
                  $('html').css('overflow-y', 'hidden');
              });

              if (!!data.data && !!data.data.field && !!data.data.value)
              {
                var newTokenField = document.createElement("input");
                newTokenField.type = "hidden";
                newTokenField.name = data.data.field;
                newTokenField.value = data.data.value;
                newTokenField.classList = "js-no-valid";
                form.append(newTokenField);
              }
            },
            beforeSend: function () {},
            complete: function(jqXHR, textStatus) {},
            error:  function(xhr, str){
              //alert('Возникла ошибка: ' + xhr.responseCode);
            }
          });
        }, 350);
      }
    });

    popupFormFestival.on('blur', '[data-field-form-festival]', function () {
      var $this = $(this),
          _valueField = $this.val();

        if(_valueField) {
          // Отправляем аналитику при потере фокуса в поле формы
          ga('send', 'event', {
            eventCategory: 'fest_fillform',
            eventAction: 'blur',
            eventLabel: $this.attr('name'),
            fieldsObject: {
              valueField: _valueField
            }
          });
        }
    });

    popupFormFestival.on('change', '[data-checkbox-form-festival]', function () {
      var $this = $(this);

      // Отправляем аналитику при изменении чекбоксов
      ga('send', 'event', {
        eventCategory: 'fest_fillform',
        eventAction: 'blur',
        eventLabel: $this.data('checkbox-form-festival'),
        fieldsObject: {
          valueField: $this.prop('checked')
        }
      });
    });
  }

  if(popupFormFestival.length || responsePopupFormFestival.length) {
    $('[data-popup-id="form-festival"].js-open-popup').on('click', function () {
      unlock = locky.lockyOn('.js-popup-wrapper');
      $('html').css('overflow-y', 'hidden');

      ga('send', 'event', {
        eventCategory: 'fest_go',
        eventAction: 'click',
        eventLabel: $(this).data('type-block-fest')
      });
    });

    $('[data-popup="form-festival"] .js-close-popup, [data-popup="response-form-festival"] .js-close-popup').on('click', function () {
      unlock();
      $('html').removeAttr('style');

      $('[data-popup="form-festival"]').find('form-group.form-group_error').removeClass('form-group_error');
      $('[data-popup="form-festival"]').find('input.error').removeClass('error');
      $('[data-popup="form-festival"]').find('label.error').remove();
    });

    $('.js-popup-wrapper').on('click', function () {
      var $this = $(this);

      if($this.find('[data-popup="form-festival"].opened') || $this.find('[data-popup="response-form-festival"].opened')) {
        unlock();
        $('html').removeAttr('style');
      }
    });
  }
});