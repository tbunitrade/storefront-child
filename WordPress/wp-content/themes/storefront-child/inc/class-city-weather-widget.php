<?php
class City_Weather_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'city_weather_widget',
            __('City Weather'),
            array('description' => __('Отображает город и текущую температуру'))
        );
    }

    public function widget($args, $instance) {
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        $city_name = get_the_title($city_id);
        $latitude = get_post_meta($city_id, 'city_latitude', true);
        $longitude = get_post_meta($city_id, 'city_longitude', true);

        // Запрос к API для получения температуры
        $temperature = ''; // Получаем из API

        echo $args['before_widget'];
        if (!empty($city_name)) {
            echo $args['before_title'] . $city_name . $args['after_title'];
        }
        echo '<p>' . __('Температура: ') . $temperature . '°C</p>';
        echo $args['after_widget'];
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
