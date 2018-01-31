(function() {
    if (window.FourPawsFrontOfficeCardHistoryComponent) {
        return;
    }

    window.FourPawsFrontOfficeCardHistoryComponent = function(params) {
        this.siteId = params.siteId || '';
        this.siteTemplateId = params.siteTemplateId || '';
        this.template = params.template || '';
        this.componentPath = params.componentPath || '';
        this.containerSelector = params.containerSelector || '';
        this.parameters = params.parameters || '';
        this.sessid = params.parameters || '';
    };

    window.FourPawsFrontOfficeCardHistoryComponent.prototype = {
        sendRequest: function(sendData, requestParams) {
            var requestUrl = requestParams.requestUrl || this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : '');
            var requestType = requestParams.type || 'POST';
            var requestDataType = requestParams.dataType || 'html';
            var callbackError = requestParams.callbackError || function(jqXHR, textStatus, component) {alert(jqXHR.status);};
            var callbackComplete = requestParams.callbackComplete || function(jqXHR, textStatus, component) {};
            var this_ = this;

            var ajaxContext = {
                siteId: this.siteId,
                siteTemplateId: this.siteTemplateId,
                componentPath: this.componentPath,
                template: this.template,
                parameters: this.parameters,
                dataType: requestDataType
            };

            sendData = sendData || {};
            sendData.ajaxContext = ajaxContext;

            $.ajax({
                type: requestType,
                dataType: requestDataType,
                url: requestUrl,
                data: sendData,
                error: function(jqXHR, textStatus) {
                    return callbackError(jqXHR, textStatus, this_);
                },
                complete: function(jqXHR, textStatus) {
                    return callbackComplete(jqXHR, textStatus, this_);
                }
            });
        }
    };
})();
