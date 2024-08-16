console.log('init');

jQuery(document).ready(function($) {
    console.log('init');

    // Обрабатываем событие ввода в поле поиска
    $('#city-search-input').on('input', function() {
        var searchTerm = $(this).val();
        console.log('Search Term: ', searchTerm);

        // Выполняем AJAX-запрос при каждом изменении текста
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'city_search',
                term: searchTerm,
                nonce: ajax_object.nonce
            },
            success: function(response) {
                console.log('ajax success', response);
                $('#cities-table tbody').html('<tr><td colspan="3">' + response.data.message + '</td></tr>');
            
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });
});


console.log('init ok');