$(document).ready(function () {
    let html = window.FourPawsCityDeliveryInfoComponentHtml || '';
    if (html) {
        let $intervalTable = $('.b-tab-shipping__inline-table.js-interval-list');
        $intervalTable.find('.b-tab-shipping__tbody').append(window.FourPawsCityDeliveryInfoComponentHtml);
        $intervalTable.show();
    }
});
