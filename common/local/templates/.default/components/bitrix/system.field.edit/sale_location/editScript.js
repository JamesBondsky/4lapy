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
                // propName = 'FIELDS['+$(this).closest('tr.main-grid-row').data('id')+']['+$(this).parent().data('name')+']';
                if (!BX.util.in_array(propName, window['LocationTypePropMulty'])) {
                    window['LocationTypePropMulty'].push(propName);
                }
                html = $(this).html();
                var firstInput = $(this).find('input:first');
                // var beginVal = firstInput.val();
                firstInput.val('');
                // var realInputName = $(this).data('realinputname');
                // var tmpInputName = realInputName.replace('[', '_').replace(']', '_');
                res  = BX.processHTML(html);
                if (!!res.SCRIPT && res.SCRIPT.length > 0) {
                    BX.ajax.processScripts(res.SCRIPT);
                    // $(this).find('bx-ui-slss-input-pool input[name="'+tmpInputName+'[L]"]').val(beginVal);
                    // initPropLocationRealVals(tmpInputName, realInputName);
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
                // propName = 'FIELDS['+$(this).closest('tr.main-grid-row').data('id')+']['+$(this).parent().data('name')+']';
                propName = $(this).parent().data('name');
                if (!BX.util.in_array(propName, window['LocationTypeProp'])) {
                    window['LocationTypeProp'].push(propName);
                }
                html = $(this).html();
                res  = BX.processHTML(html);
                // console.log(res, 'res');
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
        // console.log(eventArgs.data.FIELDS, 'eventArgs.data.FIELDS');
        if (!!eventArgs.data.FIELDS) {
            var index, el, fieldCode;
            // console.log(window['LocationTypeProp'], "window['LocationTypeProp']");
            // console.log(window['LocationTypePropMulty'], "window['LocationTypePropMulty']");
            for (index in eventArgs.data.FIELDS) {
                if (eventArgs.data.FIELDS.hasOwnProperty(index)) {
                    el = eventArgs.data.FIELDS[index];
                    if (!!el) {
                        for (fieldCode in el) {
                            // console.log(fieldCode, "fieldCode");
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
            // eventArgs.data.FIELDS.length.forEach(function(element){
            //
            // });
        }
        //console.log(gridClass, 'gridClass');
        //console.log(eventArgs, 'eventArgs');
        // console.log(window['haveLocationTypeProp'], "window['haveLocationTypeProp']");
    }
}

BX.addCustomEvent(
    window,
    "Grid::beforeRequest",
    beforeRequestPropLocation
);