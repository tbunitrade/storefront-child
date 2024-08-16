<?php

class City_Updater {

    public function init() {
        // Регистрация кастомного типа записи
        add_action('init', [$this, 'register_city_post_type']);
        
        // Регистрация таксономии "Страны"
        add_action('init', [$this, 'register_country_taxonomy']);

        // Хук для обновления температуры каждые 55 минут
        add_action('wp', [$this, 'schedule_temperature_updates']);
        
        // Регистрация настройки для API ключа
        add_action('admin_init', [$this, 'register_api_key_setting']);
    }

    public function register_city_post_type() {
        register_post_type('city', [
            'labels' => [
                'name' => __('Города'),
                'singular_name' => __('Город'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'custom-fields'],
        ]);
    }

    public function register_country_taxonomy() {
        register_taxonomy('country', 'city', [
            'labels' => [
                'name' => __('Страны'),
                'singular_name' => __('Страна'),
            ],
            'hierarchical' => true,
            'public' => true,
        ]);
    }

    public function schedule_temperature_updates() {
        if (!wp_next_scheduled('update_city_temperatures')) {
            wp_schedule_event(time(), '55_minutes', 'update_city_temperatures');
        }
    }

    public function register_api_key_setting() {
        register_setting('general', 'openweathermap_api_key', [
            'type' => 'string',
            'description' => __('API ключ для OpenWeatherMap'),
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => false,
        ]);

        add_settings_field(
            'openweathermap_api_key',
            __('OpenWeatherMap API ключ'),
            [$this, 'api_key_field_html'],
            'general'
        );
    }

    public function api_key_field_html() {
        $api_key = get_option('openweathermap_api_key', '');
        echo '<input type="text" name="openweathermap_api_key" value="' . esc_attr($api_key) . '" />';
    }
    
    public function update_city_temperatures() {
        $api_key = get_option('openweathermap_api_key');
        if (!$api_key) {
            return;
        }

        $cities = get_posts([
            'post_type' => 'city',
            'posts_per_page' => -1,
        ]);

        foreach ($cities as $city) {
            $city_name = get_the_title($city->ID);
            $country = wp_get_post_terms($city->ID, 'country', ['fields' => 'names']);
            $country_name = $country ? $country[0] : '';

            $response = wp_remote_get("http://api.openweathermap.org/data/2.5/weather?q={$city_name},{$country_name}&appid={$api_key}&units=metric");

            if (is_wp_error($response)) {
                continue;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($data['main']['temp'])) {
                update_post_meta($city->ID, 'temperature', $data['main']['temp']);
            }
        }
    }
}

add_action('update_city_temperatures', [$city_updater, 'update_city_temperatures']);
