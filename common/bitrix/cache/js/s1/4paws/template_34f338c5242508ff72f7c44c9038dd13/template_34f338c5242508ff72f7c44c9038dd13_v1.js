
; /* Start:"a:4:{s:4:"full";s:77:"/local/components/local/users.list/templates/.default/script.js?1580484086297";s:6:"source";s:63:"/local/components/local/users.list/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
$(document).ready(function() {
    $("#pagination a").live('click',function(e){
        e.preventDefault();
        $("#target-content").html('loading...');
        $("#pagination a").removeClass('active');
        var href = this.href;
        $("#target-content").load(href);
    });
});
/* End */
;; /* /local/components/local/users.list/templates/.default/script.js?1580484086297*/
