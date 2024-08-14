<?php

class City_Manager {

    public function __construct() {
        add_action('init', [$this, 'register_city_post_type']);
        add_action('init', [$this, 'register_country_taxonomy']);
        add_action('add_meta_boxes', [$this, 'add_city_meta_boxes']);
        add_action('save_post', [$this, 'save_city_meta']);
        add_action('widgets_init', [$this, 'register_city_weather_widget']);
        add_action('wp_ajax_city_search', [$this, 'ajax_city_search']);
        add_action('wp_ajax_nopriv_city_search', [$this, 'ajax_city_search']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    // Регистрация пользовательского типа записи
    public function register_city_post_type() {
        register_post_type('city',
            array(
                'labels' => array(
                    'name' => __('Города'),
                    'singular_name' => __('Город')
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'cities'),
                'supports' => array('title', 'editor', 'thumbnail')
            )
        );
    }

    // Регистрация таксономии "Страны"
    public function register_country_taxonomy() {
        register_taxonomy(
            'country',
            'city',
            array(
                'label' => __('Страны'),
                'rewrite' => array('slug' => 'country'),
                'hierarchical' => true,
            )
        );
    }

    // Добавление мета-бокса для широты и долготы
    public function add_city_meta_boxes() {
        add_meta_box(
            'city_coordinates',
            __('Coordinates'),
            [$this, 'render_city_coordinates_meta_box'],
            'city',
            'side',
            'default'
        );
    }

    // Отображение мета-бокса
    public function render_city_coordinates_meta_box($post) {
        $latitude = get_post_meta($post->ID, 'city_latitude', true);
        $longitude = get_post_meta($post->ID, 'city_longitude', true);
        ?>
        <label for="city_latitude"><?php _e('Широта:'); ?></label>
        <input type="text" id="city_latitude" name="city_latitude" value="<?php echo esc_attr($latitude); ?>" />
        <br/>
        <label for="city_longitude"><?php _e('Долгота:'); ?></label>
        <input type="text" id="city_longitude" name="city_longitude" value="<?php echo esc_attr($longitude); ?>" />
        <?php
    }

    // Сохранение мета-полей
    public function save_city_meta($post_id) {
        if (isset($_POST['city_latitude'])) {
            update_post_meta($post_id, 'city_latitude', sanitize_text_field($_POST['city_latitude']));
        }
        if (isset($_POST['city_longitude'])) {
            update_post_meta($post_id, 'city_longitude', sanitize_text_field($_POST['city_longitude']));
        }
    }

    // Регистрация виджета
    public function register_city_weather_widget() {
        register_widget('City_Weather_Widget');
    }

    // Поиск городов через AJAX
    public function ajax_city_search() {
        // Логика для поиска и возврата результатов
    }

    // Подключение скриптов
    public function enqueue_scripts() {
        wp_enqueue_script('city-search', get_stylesheet_directory_uri() . '/js/city-search.js', array('jquery'), null, true);
        wp_localize_script('city-search', 'ajaxurl', admin_url('admin-ajax.php'));
    }
}

// Инициализация класса
new City_Manager();
