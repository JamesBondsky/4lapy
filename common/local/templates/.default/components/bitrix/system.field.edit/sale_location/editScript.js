if(typeof afterShowEditLocationPropMultiple !== "function"){
    function afterShowEditLocationPropMultiple(){
        $('body').on("click", ".adm-list-table-checkbox label", function(){
            $(this).parent().find("input").click();
        });
        var items = $('.location_type_prop_multi_html');
        if(items.length>0){
            var html, res;
            items.each(function(){
                html = $(this).html();
                res = BX.processHTML(html);
                console.log(res, 'res');
                if(!!res.SCRIPT && res.SCRIPT.length > 0) {
                    BX.ajax.processScripts(res.SCRIPT);
                }
            })
        }
    }
}

BX.addCustomEvent(
    window,
    "Grid::thereEditedRows",
    afterShowEditLocationPropMultiple
);

if(typeof afterShowEditLocationProp !== "function"){
    function afterShowEditLocationProp(){
        var items = $('.location_type_prop_html');
        if(items.length>0){
            var html, res;
            items.each(function(){
                html = $(this).html();
                res = BX.processHTML(html);
                console.log(res, 'res');
                if(!!res.SCRIPT && res.SCRIPT.length > 0) {
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
//BX.onCustomEvent(window, 'Grid::thereEditedRows', []);