$('#table_UF_PROP_COND select').change(function(){
    var self = $(this),
        value = self.val();

    $.ajax({
        url: 'http://4lapy.e.adv.ru/ajax/autosort/property-hint/' + value,
        type: 'get',
        dataType: 'json',
        success: function (data) {
            if(data.success) {
                self.next('select').remove();
                var html = '<select class="property_hint">';
                $.each(data.data, function(k, v) {
                    html += '<option value="' + v.value + '">' + v.name + '</option>';
                });
                html += '</select>';
                self.after(html);
            }
            else {
                console.log('Не справочник!');
            }
        }
    });
});

$('select.property_hint').on('change', function(){
    var self = $(this),
        value = self.val();
    console.log(value);
    self.next('input').val(value);
});
