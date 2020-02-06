
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
;; /* /local/components/fourpaws/information.popup/templates/.default/script.js?1571388299710*/
