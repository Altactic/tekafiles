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
        var locked = $link.hasClass('locked');

        if(locked){
            return false;
        }
        else{
            var url = $link.attr("href");

            var text = "You are about to download confidential information. You may only download this file once. If you need to download it again, please contact tekapef@tekacap.com";
            
            modal({
				type: 'confirm',
				title: 'Warning',
				text: text,
                buttonText: {
					yes: 'Accept',
					cancel: 'Cancel'
				},
				callback: function(result) {
					if(result){
                        $link.addClass('locked');
                        $link.attr("href", "#");

                        window.location = url;
                    }
				}
			});
        }
    });
});