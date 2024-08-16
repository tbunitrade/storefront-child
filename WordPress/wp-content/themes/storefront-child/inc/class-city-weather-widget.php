<?php

class City_Weather_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'city_weather_widget', // ID виджета
            __('City Weather', 'storefront-child'), // Название виджета
            array('description' => __('Displays a list of cities and their current temperature', 'storefront-child')) // Описание виджета
        );
    }

    // Вывод виджета на фронтенде
    public function widget($args, $instance) {
        echo $args['before_widget'];

        // Получаем API-ключ из настроек
        $api_key = get_option('city_temperature_api_key');
        
        // Запрашиваем все города
        $cities_query = new WP_Query(array(
            'post_type' => 'city',
            'posts_per_page' => -1, // Получаем все города без пагинации
            'post_status' => 'publish', // Только опубликованные города
        ));

        if ($cities_query->have_posts()) {
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
                        if (isset($data['coord']['lat']) && isset($data['coord']['lon'])) {
                            $latitude = $data['coord']['lat'];
                            $longitude = $data['coord']['lon'];
                            update_post_meta(get_the_ID(), 'city_latitude', $latitude);
                            update_post_meta(get_the_ID(), 'city_longitude', $longitude);
                        }
                    }
                }

                // Запрашиваем температуру для текущего города
                $temperature = 'N/A';
                if (!empty($latitude) && !empty($longitude)) {
                    $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric";
                    $response = wp_remote_get($url);

                    if (!is_wp_error($response)) {
                        $data = json_decode(wp_remote_retrieve_body($response), true);
                        if (isset($data['main']['temp'])) {
                            $temperature = $data['main']['temp'] . '°C'; // Добавляем символ °C к температуре
                        }
                    }
                }

                // Выводим название города и его температуру
                echo '<li>' . esc_html($city_name) . ': ' . esc_html($temperature) . '</li>';
            }
            echo '</ul>';
        } else {
            echo __('No cities found.', 'storefront-child'); // Сообщение, если города не найдены
        }

        wp_reset_postdata();
        echo $args['after_widget'];
    }

    // Форма настроек виджета в админке
    public function form($instance) {
        echo '<p>' . __('This widget displays a list of cities and their current temperature.', 'storefront-child') . '</p>';
    }

    // Обновление настроек виджета
    public function update($new_instance, $old_instance) {
        return $instance;
    }
}

// Регистрация виджета
function register_city_weather_widget() {
    register_widget('City_Weather_Widget');
}
add_action('widgets_init', 'register_city_weather_widget');
