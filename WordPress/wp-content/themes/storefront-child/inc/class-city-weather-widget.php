<?php
class City_Weather_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'city_weather_widget',
            __('City Weather'),
            array('description' => __('Displays the city name and current temperature using OpenWeatherMap API'))
        );
    }

    public function widget($args, $instance) {
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        $city_name = get_the_title($city_id);
        $latitude = get_post_meta($city_id, 'city_latitude', true);
        $longitude = get_post_meta($city_id, 'city_longitude', true);

        // Получение температуры через API OpenWeatherMap
        $api_key = '953e1e40acff2a9d942ca3dc2425c01f'; // Замени на свой API ключ
        $temperature = $this->get_city_temperature($latitude, $longitude, $api_key);

        echo $args['before_widget'];
        if (!empty($city_name)) {
            echo $args['before_title'] . $city_name . $args['after_title'];
        }
        echo '<p>' . __('Температура: ') . $temperature . '°C</p>';
        echo $args['after_widget'];
    }

    private function get_city_temperature($latitude, $longitude, $api_key) {
        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&units=metric&appid={$api_key}";
    
        $response = wp_remote_get($url);
    
        if (is_wp_error($response)) {
            return __('Ошибка получения данных', 'storefront-child');
        }
    
        $data = wp_remote_retrieve_body($response);
    
        // Выведем полный ответ API для отладки
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    
        $data = json_decode($data, true);
    
        if (isset($data['main']['temp'])) {
            return $data['main']['temp'];
        } else {
            return __('Не удалось получить температуру', 'storefront-child');
        }
    }
    

    public function form($instance) {
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        $cities = get_posts(array('post_type' => 'city', 'numberposts' => -1));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('city_id'); ?>"><?php _e('Выберите город:'); ?></label>
            <select id="<?php echo $this->get_field_id('city_id'); ?>" name="<?php echo $this->get_field_name('city_id'); ?>">
                <?php foreach ($cities as $city) { ?>
                    <option value="<?php echo esc_attr($city->ID); ?>" <?php selected($city_id, $city->ID); ?>><?php echo esc_html($city->post_title); ?></option>
                <?php } ?>
            </select>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['city_id'] = (!empty($new_instance['city_id'])) ? strip_tags($new_instance['city_id']) : '';
        return $instance;
    }
}
