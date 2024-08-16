<?php
// Регистрация кастомного типа записи "Cities"
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

// Регистрация кастомной таксономии "Countries"
function register_country_taxonomy() {
    $labels = array(
        'name'              => _x('Countries', 'taxonomy general name', 'storefront-child'),
        'singular_name'     => _x('Country', 'taxonomy singular name', 'storefront-child'),
        'search_items'      => __('Search Countries', 'storefront-child'),
        'all_items'         => __('All Countries', 'storefront-child'),
    );
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
    );
    register_taxonomy('country', array('city'), $args);
}
add_action('init', 'register_country_taxonomy', 0);

// Добавление мета-боксов для широты и долготы
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
    if (array_key_exists('city_latitude', $_POST)) {
        update_post_meta($post_id, 'city_latitude', sanitize_text_field($_POST['city_latitude']));
    }
    if (array_key_exists('city_longitude', $_POST)) {
        update_post_meta($post_id, 'city_longitude', sanitize_text_field($_POST['city_longitude']));
    }
}
add_action('save_post_city', 'save_city_coordinates');


// Добавление страницы настроек для API-ключа
function city_temperature_settings_init() {
    add_settings_section(
        'city_temperature_settings_section',
        __('Настройки API OpenWeatherMap', 'storefront-child'),
        null,
        'general'
    );

    add_settings_field(
        'city_temperature_api_key',
        __('API Key OpenWeatherMap', 'storefront-child'),
        'city_temperature_api_key_field',
        'general',
        'city_temperature_settings_section'
    );

    register_setting('general', 'city_temperature_api_key', 'esc_attr');
}

function city_temperature_api_key_field() {
    $value = get_option('city_temperature_api_key', '');
    echo '<input type="text" id="city_temperature_api_key" name="city_temperature_api_key" value="' . esc_attr($value) . '" class="regular-text">';
}

add_action('admin_init', 'city_temperature_settings_init');

// Подключение виджета
require_once get_stylesheet_directory() . '/inc/class-city-weather-widget.php';
