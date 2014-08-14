jQuery(document).ready(function($){

    $('#tekafiles-accordion a').on('click', function (e) {
        if ($(this).hasClass('locked')) {
            e.preventDefault();
            return false;
        }
        else {
            $(this).addClass('locked');
        }
    });

});