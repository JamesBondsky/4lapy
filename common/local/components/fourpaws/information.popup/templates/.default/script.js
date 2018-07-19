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