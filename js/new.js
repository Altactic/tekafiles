jQuery(document).ready(function ($) {

    var form = $('form');
    var userDatalist = $("#user-datalist");
    var categoryDatalist = $("#category-datalist");
    var usersRow = $('#users-row');
    var users = $('#users');

    $('#public').on('change', function() {
        if (!$(this).prop('checked')) {
            usersRow.fadeIn();
            users.prop('required', true);
        }
        else {
            users.prop('required', false);
            usersRow.fadeOut();
        }
    });

    $('#category').on('keyup', function () {
        $.ajax({
            type: 'post',
            url: ajax.url,
            data: {
                action: 'search_categories',
                search: $(this).val()
            },
            success: function (response) {
                categoryDatalist.html(response);
            }
        });
    });

});