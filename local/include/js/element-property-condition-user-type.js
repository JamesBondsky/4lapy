$(document).ready(function(){
    $('#table_UF_PROP_COND select').each(function(){
        var self = $(this),
            value = self.val();

        if(self.find('option:selected').data('user-type') === 'directory') {
            sendAjax(self, value);

			setTimeout(function(){

				$('select.property_hint').each(function(){
					var self = $(this);
					self.val(self.next('input').val());
				})
			}
			, 500);
		}
    });

    $('#table_UF_PROP_COND select').change(function(){
        var self = $(this),
            value = self.val();

        sendAjax(self, value);
    });

    $('body').on('change', 'select.property_hint', function(){
        var self = $(this),
            value = self.val();
        self.next('input').val(value);
    });

    function sendAjax(self, value) {
        $.ajax({
            url: '/ajax/autosort/property-hint/' + value,
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
    };
});
