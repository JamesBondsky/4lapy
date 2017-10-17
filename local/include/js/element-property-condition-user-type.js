$(document).ready(function () {

    var syncWithPropValue = function (e) {

        $(e.target).prev('.PropertyValue').val($(e.target).val());

    };

    var getPropHintViaAjax = function (propertyId, propertyValue, propertyValueSelect) {
        $.ajax({
            url: '/ajax/autosort/property-hint/' + propertyId,
            type: 'get',
            dataType: 'json',
            success: function (data) {
                if (data.success) {

                    if (data.data.length === 0) {
                        propertyValueSelect.find('option').text('- пусто -');
                        return;
                    }

                    propertyValueSelect.find('option').remove();

                    $.each(data.data, function (k, v) {

                        var selected = v.value === propertyValue.val() ? ' selected="selected" ' : '';

                        propertyValueSelect.append('<option value="' + v.value + '" ' + selected + ' >' + v.name + '</option>');
                    });

                }
                else {
                    window.alert('Ошибка подсказки значений: ' + data.message);
                }
            }
        });
    };

    var initElemPropCondItem = function (item) {

        var propSelect = item.find('select.PropertySelect');
        var selectedOption = propSelect.find('option:selected');
        var propValue = item.find('input.PropertyValue');

        var isDirectory =
            selectedOption.data('userType') === 'directory'
            || ( selectedOption.data('propertyType') === 'E' && selectedOption.data('userType') === 'EAutocomplete' );

        item.find('select.PropertyValueSelect').remove();

        if (isDirectory) {

            propValue.hide();
            $('<select class="PropertyValueSelect" ><option>Загрузка...</option></select>').insertAfter(propValue);
            getPropHintViaAjax(selectedOption.val(), propValue, item.find('select.PropertyValueSelect'));
            item.find('select.PropertyValueSelect').on('change', syncWithPropValue);


        } else {
            propValue.show();
        }
    };

    var reAttachInitersOnChange = function () {
        //Если выбрали свойство, инициализировать соответствующее поле.
        $('.ElemPropCondItem .PropertySelect').off('.ElemPropCond').on('change.ElemPropCond', function (e) {
            initElemPropCondItem($(e.target).parent('.ElemPropCondItem'));
        });
    };

    reAttachInitersOnChange();

    //Если страница догрузилась, проинициализировать все поля.
    $.each($('.ElemPropCondItem'), function (k, v) {
        initElemPropCondItem($(v));
    });


    //Если нажали "Добавить", навесить обработчик на новое поле
    $('.ElemPropCondItem').parents('table').find('input[type="button"][value="Добавить"]').on('click', function () {
        window.setTimeout(function () {
            reAttachInitersOnChange();
        }, 300);
    });

});
