<?php
/*
Template Name: Список городов
*/

get_header(); ?>

<div class="city-search">
    <input type="text" id="city-search-input" placeholder="Поиск городов...">
    <button id="city-search-btn">Найти</button>
</div>

<table id="cities-table">
    <thead>
        <tr>
            <th><?php _e('Страна', 'storefront-child'); ?></th>
            <th><?php _e('Город', 'storefront-child'); ?></th>
            <th><?php _e('Температура', 'storefront-child'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $cities_query = new WP_Query(array(
            'post_type' => 'city',
            'posts_per_page' => -1,
        ));
        if ($cities_query->have_posts()) {
            while ($cities_query->have_posts()) {
                $cities_query->the_post();
                $country = wp_get_post_terms(get_the_ID(), 'country', array("fields" => "names"));
                $city_name = get_the_title();
                $temperature = get_post_meta(get_the_ID(), 'city_temperature', true); // Получаем сохраненную температуру

                echo '<tr>';
                echo '<td>' . esc_html($country[0]) . '</td>';
                echo '<td>' . esc_html($city_name) . '</td>';
                echo '<td>' . esc_html($temperature) . '°C</td>'; // Отображаем температуру
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3">No cities found.</td></tr>';
        }
        ?>
    </tbody>

</table>

<?php get_footer(); ?>
