jQuery(document).ready(function($) {
    $('#city-search-btn').on('click', function() {
        var searchTerm = $('#city-search-input').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'city_search',
                term: searchTerm
            },
            success: function(response) {
                $('#cities-table tbody').html(response);
            }
        });
    });
});
