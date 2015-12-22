jQuery(document).ready(function($){ 
    $("#accordion").accordion({
        active: false,
        collapsible: true,
        autoHeight: false,
        heightStyle: "content"
    });
    
    $(".lnk-download").on("click", function(e){
        e.preventDefault();
        var $link = $(this);
        var url = $link.attr("href");
        window.location = url;
    });
});