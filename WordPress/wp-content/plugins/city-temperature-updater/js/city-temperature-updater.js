jQuery(document).ready(function($) {
    $('#city-temperature-reset-button').on('click', function() {
        var button = $(this);
        button.prop('disabled', true);
        $('#reset-result').text('Обновление температуры...');

        $.post(ajaxurl, {
            action: 'city_temperature_updater_reset'
        }, function(response) {
            button.prop('disabled', false);
            if (response.success) {
                $('#reset-result').text(response.data);
            } else {
                $('#reset-result').text(response.data);
            }
        });
    });
});
