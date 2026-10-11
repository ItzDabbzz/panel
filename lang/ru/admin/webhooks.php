<?php

return [

    'label' => 'Вебхук',
    'plural_label' => 'Вебхуки',

    'sections' => [
        'details' => 'Сведения',
        'details_description' => 'Вебхуки сообщают другому сервису о событии в тот момент, когда оно происходит в панели.',
        'delivery' => 'Доставка',
        'delivery_description' => 'Куда отправляются события и как получатель может им доверять.',
        'message' => 'Сообщение Discord',
        'message_description' => 'Что публикуется в канале для каждого события.',
        'events' => 'События',
        'events_description' => 'Выберите, о чём сообщать этому вебхуку. Нужно хотя бы одно событие.',
    ],

    'fields' => [
        'name' => 'Название',
        'description' => 'Описание',
        'enabled' => 'Включён',
        'endpoint' => 'URL конечной точки',
        'type' => 'Тип',
        'secret' => 'Секрет подписи',
        'headers' => 'Свои заголовки',
        'header_name' => 'Заголовок',
        'header_value' => 'Значение',
        'message_username' => 'Отображаемое имя',
        'message_avatar' => 'URL аватара',
        'message_content' => 'Сообщение',
        'message_title' => 'Заголовок эмбеда',
        'message_description' => 'Описание эмбеда',
        'message_color' => 'Цвет эмбеда',
        'message_footer' => 'Подвал эмбеда',
        'message_timestamp' => 'Показывать время события',
    ],

    'placeholders' => [
        'name' => 'Синхронизация биллинга',
        'description' => 'Для чего нужен этот вебхук.',
    ],

    'helpers' => [
        'secret' => 'Если задан, каждый запрос содержит заголовок X-Webhook-Signature со значением "sha256=" и HMAC тела запроса, вычисленным с этим секретом.',
        'message_variables' => 'Используйте {{label}} для названия события, {{summary}} для предложения с его описанием, {{event}}, {{timestamp}}, {{panel}} и {{panel_url}}. Можно использовать и любые данные события, например {{data.name}} или {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Стандартный',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Записи',
        'auth' => 'Входы в систему',
        'account' => 'Действия с аккаунтом',
        'server_power' => 'Питание и консоль сервера',
        'server_files' => 'Файлы сервера',
        'server_backups' => 'Резервные копии сервера',
        'server_databases' => 'Базы данных сервера',
        'server_schedules' => 'Расписания сервера',
        'server_settings' => 'Настройки сервера',
        'admin' => 'Администрирование',
    ],

    'resources' => [
        'server' => 'Сервер',
        'user' => 'Пользователь',
        'subuser' => 'Субпользователь',
        'node' => 'Узел',
        'location' => 'Локация',
        'nest' => 'Nest',
        'egg' => 'Egg',
        'mount' => 'Точка монтирования',
        'database_host' => 'Хост баз данных',
        'database' => 'База данных',
        'backup' => 'Резервная копия',
        'schedule' => 'Расписание',
        'role' => 'Роль',
        'alert' => 'Оповещение',
    ],

    'model_actions' => [
        'created' => 'создание',
        'updated' => 'обновление',
        'deleted' => 'удаление',
    ],

    'events' => [
        'model' => ':resource: :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'Система',
    ],

    'columns' => [
        'name' => 'Название',
        'type' => 'Тип',
        'endpoint' => 'Куда отправляется',
        'events' => 'События',
        'last_delivery' => 'Последняя доставка',
        'enabled' => 'Включён',
    ],

    'statuses' => [
        'delivered' => 'Доставлено',
        'failed' => 'Ошибка',
        'never' => 'Пока ничего не отправлено',
    ],

    'deliveries' => [
        'title' => 'Доставки',
        'label' => 'доставка',
        'plural_label' => 'доставки',
        'description' => 'Всё, что было отправлено этому вебхуку за последние :days дн.',
        'empty' => 'Пока ничего не отправлено',
        'empty_description' => 'Доставки появляются здесь, как только происходит одно из выбранных событий. Нажмите «Отправить тест», чтобы проверить сейчас.',
        'no_response' => 'Нет ответа',
        'no_body' => 'Конечная точка ничего не вернула.',
        'disabled' => 'Включите вебхук, чтобы отправить это снова.',
        'columns' => [
            'id' => 'ID доставки',
            'status' => 'Доставлено',
            'event' => 'Событие',
            'response' => 'Ответ',
            'attempts' => 'Попытки',
            'duration' => 'Длительность',
            'sent' => 'Отправлено',
            'payload' => 'Содержимое',
            'body' => 'Тело ответа',
        ],
    ],

    'actions' => [
        'test' => 'Отправить тест',
        'duplicate' => 'Дублировать',
        'redeliver' => 'Отправить снова',
        'add_header' => 'Добавить заголовок',
        'generate_secret' => 'Сгенерировать',
        'select_all' => 'Выбрать все',
        'deselect_all' => 'Снять выбор',
    ],

    'notices' => [
        'delivered' => 'Тест доставлен.',
        'failed' => 'Не удалось доставить тест.',
        'responded' => 'Конечная точка ответила :status за :ms мс.',
        'duplicated' => 'Вебхук продублирован и оставлен выключенным.',
        'redelivery_queued' => 'Событие отправляется снова.',
    ],

    'validation' => [
        'events' => 'Выберите хотя бы одно событие.',
        'header_name' => '":name" не является допустимым именем заголовка.',
        'header_reserved' => 'Заголовок ":name" задаётся панелью, и его нельзя изменить.',
    ],

    'errors' => [
        'invalid_endpoint' => 'Конечная точка не является допустимым адресом http или https.',
        'unresolvable' => 'Не удалось найти адрес для :host.',
        'private_endpoint' => ':host указывает на частный или зарезервированный адрес. Установите APP_WEBHOOK_ALLOW_PRIVATE=true, чтобы разрешить доставку внутри вашей сети.',
        'unreachable' => 'Не удалось подключиться к :host.',
        'curl_required' => 'Для доставки на имя хоста требуется расширение PHP cURL, которое не установлено.',
    ],

    'copy_suffix' => ':name (копия)',

];
