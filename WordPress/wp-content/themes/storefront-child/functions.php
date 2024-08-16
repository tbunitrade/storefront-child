<?php

function my_enqueue_scripts() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue your custom script
    wp_enqueue_script(
        'city-temperature-script',
        get_stylesheet_directory_uri() . '/js/city-temperature.js',
        array('jquery'),  // Set jQuery as a dependency
        null,
        true
    );

    // Pass ajax_url and nonce to your script
    wp_localize_script('city-temperature-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('advanced_search_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');


// Register the custom post type "Cities"
function register_city_post_type() {
    $labels = array(
        'name'                  => _x('Cities', 'Post Type General Name', 'storefront-child'),
        'singular_name'         => _x('City', 'Post Type Singular Name', 'storefront-child'),
        'menu_name'             => __('Cities', 'storefront-child'),
        'all_items'             => __('All Cities', 'storefront-child'),
        'add_new_item'          => __('Add New City', 'storefront-child'),
        'edit_item'             => __('Edit City', 'storefront-child'),
    );
    $args = array(
        'label'                 => __('City', 'storefront-child'),
        'description'           => __('A custom post type for cities', 'storefront-child'),
        'labels'                => $labels,
        'supports'              => array('title'),
        'public'                => true,
        'has_archive'           => true,
    );
    register_post_type('city', $args);
}
add_action('init', 'register_city_post_type', 0);

// Register the custom taxonomy "Countries"
function register_country_taxonomy() {
    $labels = array(
        'name'              => _x('Countries', 'taxonomy general name', 'storefront-child'),
        'singular_name'     => _x('Country', 'taxonomy singular name', 'storefront-child'),
        'search_items'      => __('Search Countries', 'storefront-child'),
        'all_items'         => __('All Countries', 'storefront-child'),
    );
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,  // Set the taxonomy as hierarchical (like categories)
        'public'            => true,
    );
    register_taxonomy('country', array('city'), $args);
}
add_action('init', 'register_country_taxonomy', 0);

// Add meta boxes for latitude and longitude
function add_city_meta_boxes() {
    add_meta_box(
        'city_coordinates',
        __('City Coordinates', 'storefront-child'),
        'render_city_coordinates_meta_box',
        'city',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_city_meta_boxes');

function render_city_coordinates_meta_box($post) {
    $latitude = get_post_meta($post->ID, 'city_latitude', true);
    $longitude = get_post_meta($post->ID, 'city_longitude', true);
    ?>
    <label for="city_latitude"><?php _e('Latitude:', 'storefront-child'); ?></label>
    <input type="text" id="city_latitude" name="city_latitude" value="<?php echo esc_attr($latitude); ?>" class="widefat">
    <label for="city_longitude"><?php _e('Longitude:', 'storefront-child'); ?></label>
    <input type="text" id="city_longitude" name="city_longitude" value="<?php echo esc_attr($longitude); ?>" class="widefat">
    <?php
}

function save_city_coordinates($post_id) {
    // Save latitude if provided manually
    if (array_key_exists('city_latitude', $_POST) && !empty($_POST['city_latitude'])) {
        update_post_meta($post_id, 'city_latitude', sanitize_text_field($_POST['city_latitude']));
    }

    // Save longitude if provided manually
    if (array_key_exists('city_longitude', $_POST) && !empty($_POST['city_longitude'])) {
        update_post_meta($post_id, 'city_longitude', sanitize_text_field($_POST['city_longitude']));
    }

    // If latitude or longitude are not provided manually, get them from the API
    if (empty($_POST['city_latitude']) || empty($_POST['city_longitude'])) {
        $city_name = get_the_title($post_id);
        $api_key = get_option('city_temperature_api_key');

        // Request to OpenWeatherMap API by city name
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric";
        $response = wp_remote_get($url);

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($data['coord']['lat']) && isset($data['coord']['lon'])) {
                update_post_meta($post_id, 'city_latitude', sanitize_text_field($data['coord']['lat']));
                update_post_meta($post_id, 'city_longitude', sanitize_text_field($data['coord']['lon']));
            }
        }
    }
}
add_action('save_post_city', 'save_city_coordinates');


// Add settings page for API key
function city_temperature_settings_init() {
    add_settings_section(
        'city_temperature_settings_section',
        __('OpenWeatherMap API Settings', 'storefront-child'),
        null,
        'general'
    );

    add_settings_field(
        'city_temperature_api_key',
        __('OpenWeatherMap API Key', 'storefront-child'),
        'city_temperature_api_key_field',
        'general',
        'city_temperature_settings_section'
    );

    register_setting('general', 'city_temperature_api_key', 'esc_attr');
}

// Add a meta box to store temperature data
function add_temperature_meta_box() {
    add_meta_box(
        'city_temperature',
        __('City Temperature', 'storefront-child'),
        'render_city_temperature_meta_box',
        'city',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_temperature_meta_box');

function render_city_temperature_meta_box($post) {
    $temperature = get_post_meta($post->ID, 'city_temperature', true);
    ?>
    <label for="city_temperature"><?php _e('Temperature:', 'storefront-child'); ?></label>
    <input type="text" id="city_temperature" name="city_temperature" value="<?php echo esc_attr($temperature); ?>" class="widefat" readonly>
    <?php
}

function city_temperature_api_key_field() {
    $value = get_option('city_temperature_api_key', '');
    echo '<input type="text" id="city_temperature_api_key" name="city_temperature_api_key" value="' . esc_attr($value) . '" class="regular-text">';
}

add_action('admin_init', 'city_temperature_settings_init');

// Include the widget class
require_once get_stylesheet_directory() . '/inc/class-city-weather-widget.php';

// Handle AJAX request for city search
function handle_ajax_city_search() {
    check_ajax_referer('advanced_search_nonce', 'nonce');

    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

    if (empty($term)) {
        wp_send_json_error(array('message' => 'Search term is empty.'));
    }

    // Search cities by name
    $args = array(
        'post_type' => 'city',
        'posts_per_page' => -1,
        's' => $term,
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        wp_send_json_error(array('message' => 'No cities found.'));
    }

    $api_key = get_option('city_temperature_api_key');
    $cities = array();

    while ($query->have_posts()) {
        $query->the_post();
        $city_name = get_the_title();

        // Request to OpenWeatherMap API by city name
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $temperature = 'N/A';
            error_log('API request error: ' . $response->get_error_message());
        } else {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            error_log('API response: ' . print_r($data, true)); // Log the API response
            $temperature = isset($data['main']['temp']) ? $data['main']['temp'] : 'N/A';
        }

        $cities[] = array(
            'name' => $city_name,
            'temperature' => $temperature
        );
    }

    wp_send_json_success(array('cities' => $cities));
    wp_die();
}

add_action('wp_ajax_city_search', 'handle_ajax_city_search');
add_action('wp_ajax_nopriv_city_search', 'handle_ajax_city_search');

// Handle AJAX request to update city temperature
function update_city_temperature() {
    check_ajax_referer('advanced_search_nonce', 'nonce');

    $city_name = sanitize_text_field($_POST['city']);
    $api_key = get_option('city_temperature_api_key');
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric";
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'API request failed.'));
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!isset($data['main']['temp'])) {
        wp_send_json_error(array('message' => 'Temperature not found.'));
    }

    $temperature = $data['main']['temp'];

    // Update temperature meta field
    $cities_query = new WP_Query(array(
        'post_type' => 'city',
        'title' => $city_name,
        'posts_per_page' => 1,
    ));

    if ($cities_query->have_posts()) {
        while ($cities_query->have_posts()) {
            $cities_query->the_post();
            update_post_meta(get_the_ID(), 'city_temperature', $temperature);
        }
    }

    wp_send_json_success(array('temperature' => $temperature));
    wp_die();
}
add_action('wp_ajax_update_city_temperature', 'update_city_temperature');
add_action('wp_ajax_nopriv_update_city_temperature', 'update_city_temperature');
