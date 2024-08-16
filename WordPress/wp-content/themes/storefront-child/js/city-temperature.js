console.log('init');

jQuery(document).ready(function($) {
    // Обновляем температуру после загрузки страницы
    $('tbody tr').each(function() {
        var city = $(this).find('td:nth-child(2)').text(); // Название города
        var row = $(this);
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'update_city_temperature',
                city: city,
                nonce: ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    row.find('td:nth-child(3)').text(response.data.temperature + '°C'); // Обновляем температуру в таблице
                }
            }
        });
    });
});


// jQuery(document).ready(function($) {
//     $('#city-search-input').on('input', function() {
//         var searchTerm = $(this).val();
//         console.log('Search Term: ', searchTerm);

//         $.ajax({
//             url: ajax_object.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'city_search',
//                 term: searchTerm,
//                 nonce: ajax_object.nonce
//             },
//             success: function(response) {
//                 console.log('ajax success', response);
                
//                 if (response.success) {
//                     var cities = response.data.cities;
//                     var html = '';

//                     cities.forEach(function(city) {
//                         html += '<tr><td>' + city.name + '</td><td>' + city.temperature + '°C</td></tr>';
//                     });

//                     $('#cities-table tbody').html(html);
//                 } else {
//                     $('#cities-table tbody').html('<tr><td colspan="2">' + response.data.message + '</td></tr>');
//                 }
//             },
//             error: function(xhr, status, error) {
//                 console.error('AJAX Error:', status, error);
//             }
//         });
//     });
// });





console.log('init ok');