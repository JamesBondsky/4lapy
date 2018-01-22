if (typeof afterShowEditLocationPropMultiple !== "function") {
    function afterShowEditLocationPropMultiple() {
        $('body').on("click", ".adm-list-table-checkbox label", function () {
            $(this).parent().find("input").click();
        });
        var items                           = $('.location_type_prop_multi_html');
        window['haveLocationTypePropMulty'] = false;
        if (items.length > 0) {
            window['haveLocationTypePropMulty'] = true;
            window['LocationTypePropMulty']     = [];
            var html, res, propName;
            items.each(function () {
                propName = $(this).parent().data('name');
                if (!BX.util.in_array(propName, window['LocationTypePropMulty'])) {
                    window['LocationTypePropMulty'].push(propName);
                }
                html = $(this).html();
                var firstInput = $(this).find('input:first');
                firstInput.val('');
                res  = BX.processHTML(html);
                if (!!res.SCRIPT && res.SCRIPT.length > 0) {
                    BX.ajax.processScripts(res.SCRIPT);
                }
            });
        }
    }
}

BX.addCustomEvent(
    window,
    "Grid::thereEditedRows",
    afterShowEditLocationPropMultiple
);

if (typeof afterShowEditLocationProp !== "function") {
    function afterShowEditLocationProp() {
        var items                      = $('.location_type_prop_html');
        window['haveLocationTypeProp'] = false;
        if (items.length > 0) {
            window['haveLocationTypeProp'] = true;
            window['LocationTypeProp']     = [];
            var html, res, propName;
            items.each(function () {
                propName = $(this).parent().data('name');
                if (!BX.util.in_array(propName, window['LocationTypeProp'])) {
                    window['LocationTypeProp'].push(propName);
                }
                html = $(this).html();
                res  = BX.processHTML(html);
                if (!!res.SCRIPT && res.SCRIPT.length > 0) {
                    BX.ajax.processScripts(res.SCRIPT);
                }
            })
        }
    }
}
BX.addCustomEvent(
    window,
    "Grid::thereEditedRows",
    afterShowEditLocationProp
);

if (typeof beforeRequestPropLocation !== "function") {
    function beforeRequestPropLocation(gridClass, eventArgs) {
        if (!!eventArgs.data.FIELDS) {
            var index, el, fieldCode;
            for (index in eventArgs.data.FIELDS) {
                if (eventArgs.data.FIELDS.hasOwnProperty(index)) {
                    el = eventArgs.data.FIELDS[index];
                    if (!!el) {
                        for (fieldCode in el) {
                            if (el.hasOwnProperty(fieldCode)
                                && (BX.util.in_array(fieldCode, window['LocationTypeProp'])
                                || BX.util.in_array(fieldCode, window['LocationTypePropMulty']))
                            ) {
                                if(BX.util.in_array(fieldCode, window['LocationTypeProp'])){
                                    eventArgs.data.FIELDS[index][fieldCode] = $('tr.main-grid-row[data-id="'+index+'"] input[type=text].dropdown-field').val()
                                }
                                else{
                                    var i = -1;
                                    if($('tr.main-grid-row[data-id="'+index+'"] input.real_inputs').length > 0) {
                                        eventArgs.data.FIELDS[index][fieldCode] = [];
                                        $('tr.main-grid-row[data-id="' + index + '"] input.real_inputs')
                                            .each(function () {
                                                i++;
                                                eventArgs.data.FIELDS[index][fieldCode][i] = $(this).val();
                                            });
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

BX.addCustomEvent(
    window,
    "Grid::beforeRequest",
    beforeRequestPropLocation
);