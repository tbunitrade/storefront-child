//console.log('init');

jQuery(document).ready(function($) {
    // Update temperatures after the page loads
    $('tbody tr').each(function() {
        var city = $(this).find('td:nth-child(2)').text(); // Get the city name from the table row
        var row = $(this); // Reference to the current table row
        
        $.ajax({
            url: ajax_object.ajax_url, // AJAX handler URL
            type: 'POST', // Use POST method
            data: {
                action: 'update_city_temperature', // The action to call on the server
                city: city, // Pass the city name
                nonce: ajax_object.nonce // Include nonce for security
            },
            success: function(response) {
                if (response.success) {
                    // Update the temperature in the third column of the current row
                    row.find('td:nth-child(3)').text(response.data.temperature + '°C');
                }
            }
        });
    });
});

//console.log('init ok');
