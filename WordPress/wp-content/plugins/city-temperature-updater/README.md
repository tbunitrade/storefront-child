# City Temperature Updater

## Описание

City Temperature Updater — это плагин WordPress, который автоматически обновляет температуру городов каждые 55 минут, используя API OpenWeatherMap. Пользователь вводит только название города и выбирает страну, а координаты и температуру плагин получает автоматически.

## Установка

1. Скопируйте папку `city-temperature-updater` в каталог `wp-content/plugins/`.
2. Активируйте плагин через админку WordPress.
3. Перейдите в раздел "Настройки -> Общие" и введите ваш API-ключ OpenWeatherMap.
4. Создайте новые записи типа "Города". Укажите название города и выберите страну.
5. Плагин автоматически получит координаты и температуру через API OpenWeatherMap и сохранит их в метаполях записи.

## Настройки

- Перейдите в раздел "Настройки -> Общие" в админке WordPress.
- Введите API-ключ OpenWeatherMap в соответствующее поле.
- Сохраните изменения. Этот ключ будет использоваться для всех запросов к OpenWeatherMap.

## Как работает

1. При создании или обновлении записи типа "Города", плагин отправляет запрос к API OpenWeatherMap и получает данные о координатах и температуре.
2. Эти данные сохраняются в метаполях `city_latitude`, `city_longitude` и `city_temperature`.
3. Каждые 55
