<?php

class City_Manager {

    public function __construct() {
        // Hook into various WordPress actions
        add_action('init', [$this, 'register_city_post_type']); // Register the custom post type
        add_action('init', [$this, 'register_country_taxonomy']); // Register the taxonomy
        add_action('add_meta_boxes', [$this, 'add_city_meta_boxes']); // Add meta boxes for latitude and longitude
        add_action('save_post', [$this, 'save_city_meta']); // Save meta fields
        add_action('widgets_init', [$this, 'register_city_weather_widget']); // Register the City Weather Widget
        add_action('wp_ajax_city_search', [$this, 'ajax_city_search']); // Handle AJAX city search for logged-in users
        add_action('wp_ajax_nopriv_city_search', [$this, 'ajax_city_search']); // Handle AJAX city search for guests
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']); // Enqueue scripts
    }

    // Register the custom post type
    public function register_city_post_type() {
        register_post_type('city',
            array(
                'labels' => array(
                    'name' => __('Cities'), // Name for the post type
                    'singular_name' => __('City') // Singular name for a single item
                ),
                'public' => true, // Make the post type public
                'has_archive' => true, // Enable archive pages
                'rewrite' => array('slug' => 'cities'), // Define the URL slug
                'supports' => array('title', 'editor', 'thumbnail') // Support title, editor, and thumbnail features
            )
        );
    }

    // Register the taxonomy "Countries"
    public function register_country_taxonomy() {
        register_taxonomy(
            'country',
            'city',
            array(
                'label' => __('Countries'), // Label for the taxonomy
                'rewrite' => array('slug' => 'country'), // Define the URL slug for the taxonomy
                'hierarchical' => true, // Set as a hierarchical taxonomy (like categories)
            )
        );
    }

    // Add a meta box for latitude and longitude
    public function add_city_meta_boxes() {
        add_meta_box(
            'city_coordinates',
            __('Coordinates'), // Title of the meta box
            [$this, 'render_city_coordinates_meta_box'], // Callback function to render the meta box
            'city',
            'side', // Position the meta box on the side
            'default'
        );
    }

    // Render the meta box
    public function render_city_coordinates_meta_box($post) {
        $latitude = get_post_meta($post->ID, 'city_latitude', true); // Get the latitude from the meta field
        $longitude = get_post_meta($post->ID, 'city_longitude', true); // Get the longitude from the meta field
        ?>
        <label for="city_latitude"><?php _e('Latitude:'); ?></label>
        <input type="text" id="city_latitude" name="city_latitude" value="<?php echo esc_attr($latitude); ?>" />
        <br/>
        <label for="city_longitude"><?php _e('Longitude:'); ?></label>
        <input type="text" id="city_longitude" name="city_longitude" value="<?php echo esc_attr($longitude); ?>" />
        <?php
    }

    // Save meta fields
    public function save_city_meta($post_id) {
        if (isset($_POST['city_latitude'])) {
            update_post_meta($post_id, 'city_latitude', sanitize_text_field($_POST['city_latitude'])); // Save latitude
        }
        if (isset($_POST['city_longitude'])) {
            update_post_meta($post_id, 'city_longitude', sanitize_text_field($_POST['city_longitude'])); // Save longitude
        }
    }

    // Register the City Weather Widget
    public function register_city_weather_widget() {
        register_widget('City_Weather_Widget'); // Register the custom widget
    }

    // Handle AJAX city search
    public function ajax_city_search() {
        // Logic for searching cities and returning results will be here
    }

    // Enqueue scripts
    public function enqueue_scripts() {
        wp_enqueue_script('city-search', get_stylesheet_directory_uri() . '/js/city-search.js', array('jquery'), null, true); // Enqueue the city search script
        wp_localize_script('city-search', 'ajaxurl', admin_url('admin-ajax.php')); // Pass the AJAX URL to the script
    }
}

// Initialize the class
new City_Manager();
