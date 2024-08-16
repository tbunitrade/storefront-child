<?php
/*
Template Name: City List
*/

get_header(); ?>

<div class="city-search">
    <input type="text" id="city-search-input" placeholder="Search cities...">
    <button id="city-search-btn">Search</button>
</div>

<table id="cities-table">
    <thead>
        <tr>
            <th><?php _e('Country', 'storefront-child'); ?></th>
            <th><?php _e('City', 'storefront-child'); ?></th>
            <th><?php _e('Temperature', 'storefront-child'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Query to fetch all cities
        $cities_query = new WP_Query(array(
            'post_type' => 'city',
            'posts_per_page' => -1, // Fetch all cities without pagination
        ));
        if ($cities_query->have_posts()) {
            while ($cities_query->have_posts()) {
                $cities_query->the_post();
                $country = wp_get_post_terms(get_the_ID(), 'country', array("fields" => "names")); // Get the country associated with the city
                $city_name = get_the_title(); // Get the city name
                $temperature = get_post_meta(get_the_ID(), 'city_temperature', true); // Get the saved temperature for the city

                echo '<tr>';
                echo '<td>' . esc_html($country[0]) . '</td>'; // Display the country
                echo '<td>' . esc_html($city_name) . '</td>'; // Display the city name
                echo '<td>' . esc_html($temperature) . '°C</td>'; // Display the temperature
                echo '</tr>';
            }
        } else {
            // Display a message if no cities are found
            echo '<tr><td colspan="3">No cities found.</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php get_footer(); ?>
