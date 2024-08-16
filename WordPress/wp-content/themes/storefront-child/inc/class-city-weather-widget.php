<?php

class City_Weather_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'city_weather_widget',
            __('City Weather', 'storefront-child'),
            array('description' => __('Displays the city name and current temperature', 'storefront-child'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];

        // Получаем API-ключ
        $api_key = get_option('city_temperature_api_key');
        
        // Получаем все города
        $cities_query = new WP_Query(array(
            'post_type' => 'city',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        if ($cities_query->have_posts()) {
            echo '<ul>';
            while ($cities_query->have_posts()) {
                $cities_query->the_post();

                $city_name = get_the_title();
                $latitude = get_post_meta(get_the_ID(), 'city_latitude', true);
                $longitude = get_post_meta(get_the_ID(), 'city_longitude', true);
                
                // Если широта и долгота не заданы вручную, запросим их через API
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

                // Запрос к API для получения температуры
                $temperature = 'N/A';
                if (!empty($latitude) && !empty($longitude)) {
                    $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric";
                    $response = wp_remote_get($url);

                    if (!is_wp_error($response)) {
                        $data = json_decode(wp_remote_retrieve_body($response), true);
                        if (isset($data['main']['temp'])) {
                            $temperature = $data['main']['temp'] . '°C';
                        }
                    }
                }

                echo '<li>' . esc_html($city_name) . ': ' . esc_html($temperature) . '</li>';
            }
            echo '</ul>';
        } else {
            echo __('No cities found.', 'storefront-child');
        }

        wp_reset_postdata();
        echo $args['after_widget'];
    }

    public function form($instance) {
        echo '<p>' . __('This widget displays the weather for cities.', 'storefront-child') . '</p>';
    }

    public function update($new_instance, $old_instance) {
        return $instance;
    }
}
