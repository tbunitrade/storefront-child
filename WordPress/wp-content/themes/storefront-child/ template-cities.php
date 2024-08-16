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
        // Создаем WP_Query для получения городов
        $args = array(
            'post_type' => 'city',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'city_latitude',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key' => 'city_longitude',
                    'compare' => 'EXISTS',
                ),
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'country',
                    'field'    => 'term_id',
                    'terms'    => get_terms(array('taxonomy' => 'country', 'fields' => 'ids')),
                ),
            ),
        );

        $cities_query = new WP_Query($args);

        if ($cities_query->have_posts()) :
            while ($cities_query->have_posts()) : $cities_query->the_post();
                $city_id = get_the_ID();
                $city_name = get_the_title();
                $latitude = get_post_meta($city_id, 'city_latitude', true);
                $longitude = get_post_meta($city_id, 'city_longitude', true);

                $country_terms = get_the_terms($city_id, 'country');
                $country_name = $country_terms ? $country_terms[0]->name : '';

                // Выводим данные
                echo '<tr>';
                echo '<td>' . esc_html($country_name) . '</td>';
                echo '<td>' . esc_html($city_name) . '</td>';
                echo '<td>' . esc_html(get_post_meta($city_id, 'city_temperature', true)) . '°C</td>';
                echo '</tr>';

            endwhile;
            wp_reset_postdata();
        else :
            echo '<tr><td colspan="3">' . __('Нет городов для отображения.', 'storefront-child') . '</td></tr>';
        endif;
        ?>
    </tbody>
</table>

<?php get_footer(); ?>
