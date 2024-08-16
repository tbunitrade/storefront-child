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
    $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
    $city_name = get_the_title($city_id);
    $latitude = get_post_meta($city_id, 'city_latitude', true);
    $longitude = get_post_meta($city_id, 'city_longitude', true);
    
    $api_key = 'd660aec3373211ec29fe7d18ac838d3e'; // Замените на свой API-ключ
    $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric";
    
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $temperature = isset($data['main']['temp']) ? $data['main']['temp'] : __('Не удалось получить температуру', 'storefront-child');

    echo $args['before_widget'];
    echo $args['before_title'] . $city_name . $args['after_title'];
    echo '<p>' . __('Температура: ') . $temperature . '°C</p>';
    echo $args['after_widget'];
}

public function form($instance) {
    $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
    $cities = get_posts(array('post_type' => 'city', 'numberposts' => -1));
    ?>
    <p>
        <label for="<?php echo $this->get_field_id('city_id'); ?>"><?php _e('Выберите город:', 'storefront-child'); ?></label>
        <select id="<?php echo $this->get_field_id('city_id'); ?>" name="<?php echo $this->get_field_name('city_id'); ?>" class="widefat">
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
