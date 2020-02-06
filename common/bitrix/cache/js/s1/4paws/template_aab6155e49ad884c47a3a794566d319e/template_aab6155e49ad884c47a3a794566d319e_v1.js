
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
;
; /* Start:"a:4:{s:4:"full";s:94:"/local/components/fourpaws/catalog.element.snippet/templates/vertical/script.js?15713882994118";s:6:"source";s:79:"/local/components/fourpaws/catalog.element.snippet/templates/vertical/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function(window) {
    if (window.FourPawsCatalogElementSnippet) {
        return;
    }

    window.FourPawsCatalogElementSnippet = function(params) {
        this.cookie_prefix = params.cookiePrefix || '';
        this.cookie_domain = params.cookieDomain || '';
        this.current_server_time = params.serverTime || '';

        if (typeof BX !== 'undefined') {
            if (!this.cookie_prefix) {
                if (typeof BX.cookie_prefix !== 'undefined') {
                    this.cookie_prefix = BX.cookie_prefix;
                }
            }
            if (!this.cookie_domain) {
                if (typeof BX.cookie_domain !== 'undefined') {
                    this.cookie_domain = BX.cookie_domain;
                }
            }
            if (!this.current_server_time) {
                if (typeof BX.current_server_time !== 'undefined') {
                    this.current_server_time = BX.current_server_time;
                }
            }
        }

        this.product = {
            id: 0,
            rcmId: ''
        };
        this.selectors = params.selectors || {};

        if (typeof params === 'object') {
            if (params.product && typeof params.product === 'object') {
                this.product.id = params.product.ID;
                this.product.rcmId = params.product.RCM_ID;
            }
        }

        if (!this.product.id) {
            return;
        }

        if (this.product.rcmId) {
            if (this.selectors.trackRecommendation) {
                jQuery('body').on(
                    'click',
                    this.selectors.trackRecommendation,
                    {_this: this},
                    function(event) {
                        var _this = event.data._this;
                        _this.rememberProductRecommendation();
                    }
                );
            }
        }
    };

    window.FourPawsCatalogElementSnippet.prototype = {
        /**
         * getCookie
         */
        getCookie: function(name) {
            var matches = document.cookie.match(
                new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)")
            );
            return matches ? decodeURIComponent(matches[1]) : null;
        },
        /**
         * rememberProductRecommendation
         */
        rememberProductRecommendation: function() {
            // save to RCM_PRODUCT_LOG
            var cookieName = this.cookie_prefix + '_RCM_PRODUCT_LOG';
            var cookie = this.getCookie(cookieName);

            var itemFound = false;

            var cItems = [];
            var cItem;

            if (cookie) {
                cItems = cookie.split('.');
            }

            var i = cItems.length;

            while (i--) {
                cItem = cItems[i].split('-');

                if (cItem[0] === this.product.id) {
                    // it's already in recommendations, update the date
                    cItem = cItems[i].split('-');

                    // update rcmId and date
                    cItem[1] = this.product.rcmId;
                    cItem[2] = this.current_server_time;

                    cItems[i] = cItem.join('-');
                    itemFound = true;
                } else {
                    if ((this.current_server_time - cItem[2]) > 3600 * 24 * 30) {
                        cItems.splice(i, 1);
                    }
                }
            }

            if (!itemFound) {
                // add recommendation
                cItems.push([this.product.id, this.product.rcmId, this.current_server_time].join('-'));
            }

            // serialize
            var plNewCookie = cItems.join('.');
            var cookieDate = new Date(new Date().getTime() + 1000 * 3600 * 24 * 365 * 10).toUTCString();

            document.cookie = cookieName + '=' + plNewCookie + '; path=/; expires=' + cookieDate + '; domain=' + this.cookie_domain;
        }
    };

})(window);

/* End */
;; /* /local/templates/.default/components/fourpaws/catalog.products.recommendations/fp.17.0.similar/script.js?15713883004582*/
; /* /local/components/fourpaws/catalog.element.snippet/templates/vertical/script.js?15713882994118*/
