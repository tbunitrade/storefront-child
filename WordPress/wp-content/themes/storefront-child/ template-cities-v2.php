<?php
/*
Template Name: City WPDB
*/

get_header();

// Добавляем кастомный хук перед таблицей
// Add Custom hook before table
do_action('before_cities_table');
?>

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
        global $wpdb;

        // Запрос для получения данных о городах и странах
        // Request go get data about cointries and their capital
        $query = "
            SELECT p.ID, p.post_title AS city_name, t.name AS country_name, pm_lat.meta_value AS latitude, pm_lon.meta_value AS longitude
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'country')
            LEFT JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
            LEFT JOIN {$wpdb->postmeta} pm_lat ON (p.ID = pm_lat.post_id AND pm_lat.meta_key = 'city_latitude')
            LEFT JOIN {$wpdb->postmeta} pm_lon ON (p.ID = pm_lon.post_id AND pm_lon.meta_key = 'city_longitude')
            WHERE p.post_type = 'city' AND p.post_status = 'publish'
        ";

        $cities = $wpdb->get_results($query);

        // Отображение городов и температур
        foreach ($cities as $city) {
            // Здесь сделаем запрос к API для получения температуры
            $temperature = ''; // Здесь добавляем логику для получения температуры по широте и долготе

            echo '<tr>';
            echo '<td>' . esc_html($city->country_name) . '</td>';
            echo '<td>' . esc_html($city->city_name) . '</td>';
            echo '<td>' . esc_html($temperature) . '°C</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>

<?php
// Добавляем кастомный хук после таблицы
do_action('after_cities_table');

get_footer();
?>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#city-search-btn').on('click', function() {
        var searchTerm = $('#city-search-input').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'city_search',
                term: searchTerm
            },
            success: function(response) {
                $('#cities-table tbody').html(response);
            }
        });
    });
});
</script>
