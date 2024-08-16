<?php

class City_Weather_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'city_weather_widget',
            __('City Weather', 'storefront-child'),
            array('description' => __('Displays the city name and current temperature', 'storefront-child'))
        );
    }

    // Output the widget content on the front-end
    public function widget($args, $instance) {
        echo $args['before_widget'];

        // Retrieve the API key from the settings
        $api_key = get_option('city_temperature_api_key');
        
        // Query to get all cities
        $cities_query = new WP_Query(array(
            'post_type' => 'city',
            'posts_per_page' => -1,
            'post_status' => 'publish', // Only fetch published cities
        ));

        if ($cities_query->have_posts()) {
            echo '<ul>';
            while ($cities_query->have_posts()) {
                $cities_query->the_post();

                $city_name = get_the_title(); // Get the city name
                $latitude = get_post_meta(get_the_ID(), 'city_latitude', true); // Retrieve the latitude
                $longitude = get_post_meta(get_the_ID(), 'city_longitude', true); // Retrieve the longitude
                
                // If latitude and longitude are not manually set, fetch them via the API
                if (empty($latitude) || empty($longitude)) {
                    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric";
                    $response = wp_remote_get($url);

                    if (!is_wp_error($response)) {
                        $data = json_decode(wp_remote_retrieve_body($response), true);
                        if (isset($data['coord']['lat']) && isset($data['coord']['lon'])) {
                            $latitude = $data['coord']['lat']; // Set the latitude
                            $longitude = $data['coord']['lon']; // Set the longitude
                            // Save the latitude and longitude to the database
                            update_post_meta(get_the_ID(), 'city_latitude', $latitude);
                            update_post_meta(get_the_ID(), 'city_longitude', $longitude);
                        }
                    }
                }

                // Fetch the temperature from the API
                $temperature = 'N/A';
                if (!empty($latitude) && !empty($longitude)) {
                    $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric";
                    $response = wp_remote_get($url);

                    if (!is_wp_error($response)) {
                        $data = json_decode(wp_remote_retrieve_body($response), true);
                        if (isset($data['main']['temp'])) {
                            $temperature = $data['main']['temp'] . '°C'; // Format the temperature with °C
                        }
                    }
                }

                // Display the city name and its temperature
                echo '<li>' . esc_html($city_name) . ': ' . esc_html($temperature) . '</li>';
            }
            echo '</ul>';
        } else {
            echo __('No cities found.', 'storefront-child'); // Message if no cities are found
        }

        wp_reset_postdata(); // Reset the global $post object
        echo $args['after_widget'];
    }

    // Display the widget settings form in the admin
    public function form($instance) {
        echo '<p>' . __('This widget displays the weather for cities.', 'storefront-child') . '</p>';
    }

    // Process widget options to save
    public function update($new_instance, $old_instance) {
        return $instance;
    }
}
