
; /* Start:"a:4:{s:4:"full";s:77:"/local/components/local/users.list/templates/.default/script.js?1580483724450";s:6:"source";s:63:"/local/components/local/users.list/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
$(document).ready(function() {
    console.log("asdasd");
    $("#target-content").load("pagination.php?page=1");
    $("#pagination li").live('click',function(e){
        e.preventDefault();
        $("#target-content").html('loading...');
        $("#pagination li").removeClass('active');
        $(this).addClass('active');
        var pageNum = this.id;
        $("#target-content").load("pagination.php?page=" + pageNum);
    });
});
/* End */
;; /* /local/components/local/users.list/templates/.default/script.js?1580483724450*/
