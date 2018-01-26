(function(window) {
    if (window.FourPawsCatalogElementSnippet) {
        return;
    }

    window.FourPawsCatalogElementSnippet = function(params) {
        this.cookie_prefix = params.cookiePrefix || '';
        this.cookie_domain = params.cookieDomain || '';
        this.current_server_time = params.serverTime || '';

        if (typeof BX != 'undefined') {
            if (!this.cookie_prefix) {
                if (typeof BX.cookie_prefix != 'undefined') {
                    this.cookie_prefix = BX.cookie_prefix;
                }
            }
            if (!this.cookie_domain) {
                if (typeof BX.cookie_domain != 'undefined') {
                    this.cookie_domain = BX.cookie_domain;
                }
            }
            if (!this.current_server_time) {
                if (typeof BX.current_server_time != 'undefined') {
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

                if (cItem[0] == this.product.id) {
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
