<?php
// Подключение стилей и скриптов
function storefront_child_enqueue_styles() {
    // Подключение стилей родительской темы
    wp_enqueue_style('storefront-style', get_template_directory_uri() . '/style.css');
    // Подключение стилей дочерней темы
    wp_enqueue_style('storefront-child-style', get_stylesheet_directory_uri() . '/style.css', array('storefront-style'), wp_get_theme()->get('Version'));

    // Подключение скриптов для AJAX-поиска
    wp_enqueue_script('city-search-script', get_stylesheet_directory_uri() . '/js/city-search.js', array('jquery'), null, true);
    wp_localize_script('city-search-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_styles');

// Подключение классов и функций
require_once get_stylesheet_directory() . '/inc/class-city-manager.php';
require_once get_stylesheet_directory() . '/inc/class-city-weather-widget.php';

// Инициализация класса управления городами
new City_Manager();

// Регистрация виджета
function storefront_child_register_widgets() {
    register_widget('City_Weather_Widget');
}
add_action('widgets_init', 'storefront_child_register_widgets');

// AJAX обработчик для поиска городов
function city_search_ajax_handler() {
    global $wpdb;

    $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

    // Запрос для поиска городов по имени
    $query = $wpdb->prepare("
        SELECT p.ID, p.post_title AS city_name, t.name AS country_name, pm_lat.meta_value AS latitude, pm_lon.meta_value AS longitude
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
        LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'country')
        LEFT JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
        LEFT JOIN {$wpdb->postmeta} pm_lat ON (p.ID = pm_lat.post_id AND pm_lat.meta_key = 'city_latitude')
        LEFT JOIN {$wpdb->postmeta} pm_lon ON (p.ID = pm_lon.post_id AND pm_lon.meta_key = 'city_longitude')
        WHERE p.post_type = 'city' AND p.post_status = 'publish' AND p.post_title LIKE %s
    ", '%' . $wpdb->esc_like($search_term) . '%');

    $cities = $wpdb->get_results($query);

    if ($cities) {
        foreach ($cities as $city) {
            // Здесь добавляем логику для получения температуры
            $temperature = ''; // Получаем из API

            echo '<tr>';
            echo '<td>' . esc_html($city->country_name) . '</td>';
            echo '<td>' . esc_html($city->city_name) . '</td>';
            echo '<td>' . esc_html($temperature) . '°C</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">' . __('Города не найдены', 'storefront-child') . '</td></tr>';
    }

    wp_die(); // Завершаем выполнение скрипта
}
add_action('wp_ajax_city_search', 'city_search_ajax_handler');
add_action('wp_ajax_nopriv_city_search', 'city_search_ajax_handler');

// Текстовый домен для перевода
function storefront_child_setup_theme() {
    load_child_theme_textdomain('storefront-child', get_stylesheet_directory() . '/languages');
}
add_action('after_setup_theme', 'storefront_child_setup_theme');

?>
