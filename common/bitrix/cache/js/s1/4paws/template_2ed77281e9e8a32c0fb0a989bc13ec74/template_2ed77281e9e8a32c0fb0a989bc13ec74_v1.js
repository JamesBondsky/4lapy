
; /* Start:"a:4:{s:4:"full";s:87:"/local/components/fourpaws/information.popup/templates/.default/script.js?1571388299710";s:6:"source";s:73:"/local/components/fourpaws/information.popup/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
$(window).on('load', function () {
    if (window.fourPawsErrorList && typeof window.popupAddAndEdit === 'function') {
        let errors = window.fourPawsErrorList.errors || [];
        let notices = window.fourPawsErrorList.notices || [];
        let message = '';

        if (notices.length) {
            message = notices.join(' ');
        }

        if (!errors.length) {
            if (message) {
                window.popupAddAndEdit({success: true, message: message});
            }
        } else {
            if (message) {
                errors.push(message);
            }
            window.popupAddAndEdit({success: false, data: {errors: errors}});
        }
    }
});
/* End */
;
; /* Start:"a:4:{s:4:"full";s:104:"/local/templates/.default/components/bitrix/system.field.edit/sale_location/editScript.js?15713883004351";s:6:"source";s:89:"/local/templates/.default/components/bitrix/system.field.edit/sale_location/editScript.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
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
/* End */
;; /* /local/components/fourpaws/information.popup/templates/.default/script.js?1571388299710*/
; /* /local/templates/.default/components/bitrix/system.field.edit/sale_location/editScript.js?15713883004351*/
