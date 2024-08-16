<?php

// Добавляем кнопку сброса таймера на страницу настроек
function city_temperature_updater_settings_page() {
    add_submenu_page(
        'options-general.php',
        __('City Temperature Updater', 'city-temperature-updater'),
        __('City Temperature Updater', 'city-temperature-updater'),
        'manage_options',
        'city-temperature-updater',
        'city_temperature_updater_settings_page_content'
    );
}
add_action('admin_menu', 'city_temperature_updater_settings_page');

function city_temperature_updater_settings_page_content() {
    ?>
    <div class="wrap">
        <h1><?php _e('City Temperature Updater', 'city-temperature-updater'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('general');
            do_settings_sections('general');
            submit_button();
            ?>
        </form>
        <h2><?php _e('Сбросить таймер обновления температуры', 'city-temperature-updater'); ?></h2>
        <button id="city-temperature-reset-button" class="button button-primary">
            <?php _e('Обновить температуру сейчас', 'city-temperature-updater'); ?>
        </button>
        <div id="reset-result"></div>
    </div>
    <?php
}

// Обработчик AJAX для обновления температуры
function city_temperature_updater_ajax_handler() {
    if (current_user_can('manage_options')) {
        // Вызов функции обновления температуры
        $updater = new City_Updater();
        $updater->update_city_temperatures();

        wp_send_json_success(__('Температура обновлена.', 'city-temperature-updater'));
    } else {
        wp_send_json_error(__('У вас нет прав для выполнения этого действия.', 'city-temperature-updater'));
    }
}
add_action('wp_ajax_city_temperature_updater_reset', 'city_temperature_updater_ajax_handler');

// Подключаем скрипт для обработки нажатия кнопки
function city_temperature_updater_enqueue_scripts($hook) {
    if ($hook !== 'settings_page_city-temperature-updater') {
        return;
    }
    wp_enqueue_script('city-temperature-updater', plugins_url('js/city-temperature-updater.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'city_temperature_updater_enqueue_scripts');
