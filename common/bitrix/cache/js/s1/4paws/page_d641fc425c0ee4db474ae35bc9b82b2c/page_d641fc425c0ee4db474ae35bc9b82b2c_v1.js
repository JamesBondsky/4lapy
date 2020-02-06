
; /* Start:"a:4:{s:4:"full";s:98:"/local/components/fourpaws/city.delivery.info/templates/catalog.detail.tab/script.js?1571388299354";s:6:"source";s:84:"/local/components/fourpaws/city.delivery.info/templates/catalog.detail.tab/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
$(document).ready(function () {
    let html = window.FourPawsCityDeliveryInfoComponentHtml || '';
    if (html) {
        let $intervalTable = $('.b-tab-shipping__inline-table.js-interval-list');
        $intervalTable.find('.b-tab-shipping__tbody').append(window.FourPawsCityDeliveryInfoComponentHtml);
        $intervalTable.show();
    }
});

/* End */
;
; /* Start:"a:4:{s:4:"full";s:119:"/local/templates/.default/components/fourpaws/catalog.products.recommendations/fp.17.0.similar/script.js?15713883004582";s:6:"source";s:104:"/local/templates/.default/components/fourpaws/catalog.products.recommendations/fp.17.0.similar/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function() {
    if (window.FourPawsCatalogProductsRecommendationsComponent) {
        return;
    }

    window.FourPawsCatalogProductsRecommendationsComponent = function(params) {
        this.siteId            = params.siteId || '';
        this.ajaxId            = params.ajaxId || '';
        this.template          = params.template || '';
        this.componentPath     = params.componentPath || '';
        this.parameters        = params.parameters || '';
        this.containerSelector = params.containerSelector || '';
        this.sliderSelector    = params.sliderSelector || '';

        this.bigData = params.bigData || {enabled: false};

        if (this.bigData.enabled) {
            // эти переменные используются в FourPawsCatalogElementSnippet
            BX.cookie_prefix = this.bigData.js.cookiePrefix || '';
            BX.cookie_domain = this.bigData.js.cookieDomain || '';
            BX.current_server_time = this.bigData.js.serverTime;

            BX.ready(BX.delegate(this.bigDataLoad, this));
        } else {
            BX.ready(
                BX.delegate(
                    function() {
                        this.sendRequest({
                            action: 'deferredLoad',
                            bigData: 'N',
                            items: [],
                            rid: 0
                        });
                    },
                    this
                )
            );
        }
    };
    window.FourPawsCatalogProductsRecommendationsComponent.prototype = {
        bigDataLoad: function() {
            var url = this.bigData.requestBaseUrl + '?' + this.bigData.requestUrlParams;
            var onReady = BX.delegate(
                function (result) {
                    this.sendRequest({
                        action: 'deferredLoad',
                        bigData: 'Y',
                        items: result && result.items || [],
                        rid: result && result.id
                    });
                },
                this
            );
            BX.ajax({
                method: 'GET',
                dataType: 'json',
                url: url,
                timeout: 3,
                onsuccess: onReady,
                onfailure: onReady
            });
        },
        sendRequest: function(data) {
            var defaultData = {
                siteId: this.siteId,
                template: this.template,
                parameters: this.parameters
            };

            if (this.ajaxId) {
                defaultData.AJAX_ID = this.ajaxId;
            }
            var requestUrl = this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : '');

            BX.ajax({
                url: requestUrl,
                method: 'POST',
                dataType: 'json',
                timeout: 60,
                data: BX.merge(defaultData, data),
                onsuccess: BX.delegate(
                    function(result) {
                        if (!result) {
                            return;
                        }
                        if (result.JS) {
                            BX.ajax.processScripts(
                                BX.processHTML(result.JS).SCRIPT,
                                false,
                                BX.delegate(
                                    function () {
                                        if (result.HTML) {
                                            if (this.containerSelector) {
                                                jQuery(this.containerSelector).replaceWith(result.HTML);
                                                window._global.initPopularProductSlider(this.sliderSelector);
                                            }
                                        }
                                    },
                                    this
                                )
                            );
                        } else if (result.HTML) {
                            if (this.containerSelector) {
                                jQuery(this.containerSelector).replaceWith(result.HTML);
                                window._global.initPopularProductSlider(this.sliderSelector);
                            }
                        }
                    },
                    this
                )
            });
        }
    };
})();
/* End */
;; /* /local/components/fourpaws/city.delivery.info/templates/catalog.detail.tab/script.js?1571388299354*/
; /* /local/templates/.default/components/fourpaws/catalog.products.recommendations/fp.17.0.similar/script.js?15713883004582*/
