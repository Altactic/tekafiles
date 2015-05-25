jQuery(document).ready(function($){
    
    $("#accordion").accordion({
        active: false,
        collapsible: true,
        autoHeight: false
    });
    
    $(".lnk-download").on("click", function(e){
        e.preventDefault();
        var $link = $(this);
        var locked = $link.hasClass('locked');

        if(locked){
            return false;
        }
        else{
            var url = $link.attr("href");

            var text = "are you sure to download this file?";

            if(confirm(text)){
                
                $link.addClass('locked');
                $link.attr("href", "#");
                
                window.location = url;
            }
        }
    });
});