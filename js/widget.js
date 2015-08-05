jQuery(document).ready(function($){
    
    var trans = {
        en: {
            title:          "Warning",
            description:    "You are about to download confidential information. You may only download this file once. If you need to download it again, please contact tekapef@tekacap.com",
            accept:         "Accept",
            cancel:         "Cancel"
        },
        es: {
            title:          "Advertencia",
            description:    "Se descargará la información confidencial. Sólo puede descargar este archivo una vez. Si tiene que descargarlo de nuevo, por favor póngase en contacto con tekapef@tekacap.com",
            accept:         "Aceptar",
            cancel:         "Cancelar"
        }
    };    
    
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
        var lang = "es";
        
        if(locked){
            return false;
        }
        else{
            var url = $link.attr("href");

            modal({
				type:   'confirm',
				title:  trans[lang]["title"],
				text:   trans[lang]["description"],
                buttonText: {
					yes:    trans[lang]["accept"],
					cancel: trans[lang]["cancel"]
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