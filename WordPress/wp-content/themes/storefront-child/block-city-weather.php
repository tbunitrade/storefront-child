<?php

function render_city_weather_block($attributes) {
    // Получаем API-ключ из настроек
    $api_key = get_option('city_temperature_api_key');
    if (!$api_key) {
        error_log('API key is missing.');
        return '<p>' . __('API key is missing.', 'storefront-child') . '</p>';
    }

    // Запрашиваем все города
    $cities_query = new WP_Query(array(
        'post_type' => 'city',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    ob_start(); // Начинаем буферизацию вывода
    if ($cities_query->have_posts()) {
        error_log('Cities found: ' . $cities_query->found_posts);
        echo '<ul>';
        while ($cities_query->have_posts()) {
            $cities_query->the_post();

            $city_name = get_the_title(); // Получаем название города
            $latitude = get_post_meta(get_the_ID(), 'city_latitude', true); // Получаем широту
            $longitude = get_post_meta(get_the_ID(), 'city_longitude', true); // Получаем долготу

            // Если широта и долгота не заданы вручную, получаем их через API
            if (empty($latitude) || empty($longitude)) {
                $url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric";
                $response = wp_remote_get($url);

                if (!is_wp_error($response)) {
                    $data = json_decode(wp_remote_retrieve_body($response), true);
                    error_log('API response for city ' . $city_name . ': ' . print_r($data, true)); // Логирование ответа API
                    if (isset($data['coord']['lat']) && isset($data['coord']['lon'])) {
                        $latitude = $data['coord']['lat'];
                        $longitude = $data['coord']['lon'];
                        update_post_meta(get_the_ID(), 'city_latitude', $latitude);
                        update_post_meta(get_the_ID(), 'city_longitude', $longitude);
                    } else {
                        error_log('Latitude and longitude not found for city: ' . $city_name);
                    }
                } else {
                    error_log('API request error for city ' . $city_name . ': ' . $response->get_error_message());
                }
            }

            // Запрашиваем температуру для текущего города
            $temperature = 'N/A';
            if (!empty($latitude) && !empty($longitude)) {
                $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric";
                $response = wp_remote_get($url);

                if (!is_wp_error($response)) {
                    $data = json_decode(wp_remote_retrieve_body($response), true);
                    error_log('Temperature data for city ' . $city_name . ': ' . print_r($data, true)); // Логирование данных о температуре
                    if (isset($data['main']['temp'])) {
                        $temperature = $data['main']['temp'] . '°C'; // Добавляем символ °C к температуре
                    } else {
                        error_log('Temperature not found for city: ' . $city_name);
                    }
                } else {
                    error_log('API request error for temperature in city ' . $city_name . ': ' . $response->get_error_message());
                }
            }

            // Выводим название города и его температуру
            echo '<li>' . esc_html($city_name) . ': ' . esc_html($temperature) . '</li>';
        }
        echo '</ul>';
    } else {
        echo __('No cities found.', 'storefront-child'); // Сообщение, если города не найдены
        error_log('No cities found');
    }

    wp_reset_postdata(); // Восстанавливаем глобальную переменную $post
    return ob_get_clean(); // Возвращаем содержимое буфера
}
