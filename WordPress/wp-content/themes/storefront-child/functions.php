<?php

// Подключение стилей родительской темы
function storefront_child_enqueue_styles() {
    wp_enqueue_style('storefront-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('storefront-child-style', get_stylesheet_uri(), ['storefront-style']);

    // Подключаем jQuery
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_styles');

// Создание кастомного типа записи "Cities"
function create_cities_post_type() {
    register_post_type('city', [
        'labels' => [
            'name' => __('Cities'),
            'singular_name' => __('City'),
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'custom-fields'],
        'menu_icon' => 'dashicons-location',
    ]);
}
add_action('init', 'create_cities_post_type');

// Создание метабокса для широты и долготы
function add_city_meta_boxes() {
    add_meta_box('city_coordinates', 'City Coordinates', 'render_city_coordinates_meta_box', 'city', 'side', 'default');
}
add_action('add_meta_boxes', 'add_city_meta_boxes');

function render_city_coordinates_meta_box($post) {
    $latitude = get_post_meta($post->ID, '_city_latitude', true);
    $longitude = get_post_meta($post->ID, '_city_longitude', true);
    ?>
    <label for="city_latitude">Latitude:</label>
    <input type="text" id="city_latitude" name="city_latitude" value="<?php echo esc_attr($latitude); ?>" />
    <br />
    <label for="city_longitude">Longitude:</label>
    <input type="text" id="city_longitude" name="city_longitude" value="<?php echo esc_attr($longitude); ?>" />
    <?php
}

function save_city_coordinates($post_id) {
    if (array_key_exists('city_latitude', $_POST)) {
        update_post_meta($post_id, '_city_latitude', sanitize_text_field($_POST['city_latitude']));
    }
    if (array_key_exists('city_longitude', $_POST)) {
        update_post_meta($post_id, '_city_longitude', sanitize_text_field($_POST['city_longitude']));
    }
}
add_action('save_post', 'save_city_coordinates');

// Создание таксономии "Countries"
function create_countries_taxonomy() {
    register_taxonomy('country', 'city', [
        'labels' => [
            'name' => __('Countries'),
            'singular_name' => __('Country'),
        ],
        'hierarchical' => true,
        'public' => true,
    ]);
}
add_action('init', 'create_countries_taxonomy');

// Добавляем интервал в 15 минут в Cron
add_filter('cron_schedules', function($schedules) {
    $schedules['15_minutes'] = [
        'interval' => 15 * 60,
        'display'  => __('Every 15 Minutes'),
    ];
    return $schedules;
});

// Обновление температуры городов по расписанию
class City_Updater {

    public function init() {
        // Запуск функции обновления по расписанию
        add_action('wp', [$this, 'schedule_temperature_updates']);
        add_action('update_city_temperatures', [$this, 'update_city_temperatures']);
        // Регистрация настройки для API ключа
        add_action('admin_init', [$this, 'register_api_key_setting']);
    }

    public function schedule_temperature_updates() {
        if (!wp_next_scheduled('update_city_temperatures')) {
            wp_schedule_event(time(), '15_minutes', 'update_city_temperatures');
        }
    }

    public function update_city_temperatures() {
        $api_key = get_option('openweathermap_api_key');
        if (!$api_key) {
            error_log('API ключ не установлен.');
            return;
        }

        $cities = get_posts([
            'post_type' => 'city',
            'posts_per_page' => -1,
        ]);

        foreach ($cities as $city) {
            $city_name = get_the_title($city->ID);
            $latitude = get_post_meta($city->ID, '_city_latitude', true);
            $longitude = get_post_meta($city->ID, '_city_longitude', true);

            error_log('Начинаем обновление данных для города: ' . $city_name);

            $response = wp_remote_get("http://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric");

            if (is_wp_error($response)) {
                error_log('Ошибка в запросе к OpenWeatherMap: ' . $response->get_error_message());
                continue;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);
            if ($data['cod'] !== 200) {
                error_log('Ошибка в ответе от OpenWeatherMap: ' . $data['message']);
                continue;
            }

            // Логируем успешное получение данных
            error_log('Данные успешно получены для города: ' . $city_name);

            // Сохранение данных в кастомные поля
            update_post_meta($city->ID, '_city_temperature', $data['main']['temp']);
            update_post_meta($city->ID, '_city_weather_description', $data['weather'][0]['description']);
            update_post_meta($city->ID, '_city_humidity', $data['main']['humidity']);
            update_post_meta($city->ID, '_city_wind_speed', $data['wind']['speed']);
            update_post_meta($city->ID, '_city_cloudiness', $data['clouds']['all']);
            update_post_meta($city->ID, '_city_pressure', $data['main']['pressure']);
            update_post_meta($city->ID, '_city_visibility', $data['visibility']);
            update_post_meta($city->ID, '_city_sunrise', $data['sys']['sunrise']);
            update_post_meta($city->ID, '_city_sunset', $data['sys']['sunset']);
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
}

$city_updater = new City_Updater();
$city_updater->init();

// Регистрация виджета для отображения температуры города
class City_Temperature_Widget extends WP_Widget {
    function __construct() {
        parent::__construct('city_temperature_widget', __('City Temperature'));
    }

    public function widget($args, $instance) {
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        if ($city_id) {
            $city_name = get_the_title($city_id);
            $latitude = get_post_meta($city_id, '_city_latitude', true);
            $longitude = get_post_meta($city_id, '_city_longitude', true);

            $api_key = get_option('openweathermap_api_key');
            $response = wp_remote_get("http://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric");

            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($data['main']['temp'])) {
                    $temperature = $data['main']['temp'];
                    echo $args['before_widget'];
                    echo $args['before_title'] . $city_name . $args['after_title'];
                    echo '<p>Current Temperature: ' . esc_html($temperature) . '°C</p>';
                    echo $args['after_widget'];
                }
            }
        }
    }

    public function form($instance) {
        $cities = get_posts(['post_type' => 'city', 'numberposts' => -1]);
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('city_id')); ?>"><?php _e('Select City:'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('city_id')); ?>" name="<?php echo esc_attr($this->get_field_name('city_id')); ?>">
                <?php foreach ($cities as $city): ?>
                    <option value="<?php echo esc_attr($city->ID); ?>" <?php selected($city_id, $city->ID); ?>><?php echo esc_html($city->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['city_id'] = (!empty($new_instance['city_id'])) ? strip_tags($new_instance['city_id']) : '';
        return $instance;
    }
}
add_action('widgets_init', function() {
    register_widget('City_Temperature_Widget');
});

// Шорткод для вывода списка городов и их температур
class Cities_List_Shortcode {
    public function __construct() {
        add_shortcode('cities_list', [$this, 'render_cities_list']);
    }

    public function render_cities_list() {
        ob_start();
    
        echo '<div class="cities-list">';
        echo '<input type="text" id="city-search" placeholder="Search Cities">';
        echo '<table id="cities-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Country</th>';
        echo '<th>City</th>';
        echo '<th>Temperature (°C)</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
    
        $cities = $this->get_cities();
    
        foreach ($cities as $city) {
            echo '<tr>';
            echo '<td>' . esc_html($city['country']) . '</td>';
            echo '<td>' . esc_html($city['city']) . '</td>';
            echo '<td>' . esc_html($city['temperature']) . '</td>';
            echo '</tr>';
        }
    
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    
        return ob_get_clean();
    }
    
    private function get_cities() {
        $cities = [];
    
        $args = [
            'post_type' => 'city',
            'post_status' => 'publish',
            'numberposts' => -1,
        ];
    
        $posts = get_posts($args);
    
        foreach ($posts as $post) {
            $country = wp_get_post_terms($post->ID, 'country', ['fields' => 'names']);
            $temperature = get_post_meta($post->ID, '_city_temperature', true);
    
            // Проверка и вывод в лог сохраненных данных
            error_log('Город: ' . $post->post_title . ' | Температура: ' . $temperature);
    
            $cities[] = [
                'city' => $post->post_title,
                'country' => $country ? $country[0] : 'N/A',
                'temperature' => $temperature ? $temperature : 'N/A',
            ];
        }
    
        return $cities;
    }
}

new Cities_List_Shortcode();

// Удалите или закомментируйте этот блок после тестирования
add_action('init', function() {
    global $city_updater;
    $city_updater->update_city_temperatures();
});
