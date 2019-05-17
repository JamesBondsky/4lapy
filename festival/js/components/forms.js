$(window).load(function(){
  var unlock;

  var popupFormFestival = $('[data-popup="form-festival"]');
  var responsePopupFormFestival = $('[data-popup="response-form-festival"]');
  if(popupFormFestival.length) {
    var timeIdPopupFormFestival;

    popupFormFestival.find('form').on('submit', function(event){
      event.preventDefault();
      event.stopPropagation();

      var form = $(this);

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