<?php

return [

    'label' => 'ವೆಬ್‌ಹುಕ್',
    'plural_label' => 'ವೆಬ್‌ಹುಕ್‌ಗಳು',

    'sections' => [
        'details' => 'ವಿವರಗಳು',
        'details_description' => 'ಪ್ಯಾನೆಲ್‌ನಲ್ಲಿ ಏನಾದರೂ ನಡೆದ ಕ್ಷಣವೇ ವೆಬ್‌ಹುಕ್‌ಗಳು ಮತ್ತೊಂದು ಸೇವೆಗೆ ತಿಳಿಸುತ್ತವೆ.',
        'delivery' => 'ವಿತರಣೆ',
        'delivery_description' => 'ಈವೆಂಟ್‌ಗಳನ್ನು ಎಲ್ಲಿಗೆ ಕಳುಹಿಸಲಾಗುತ್ತದೆ ಮತ್ತು ಸ್ವೀಕರಿಸುವವರು ಅವುಗಳನ್ನು ಹೇಗೆ ನಂಬಬಹುದು.',
        'message' => 'Discord ಸಂದೇಶ',
        'message_description' => 'ಪ್ರತಿ ಈವೆಂಟ್‌ಗೆ ಚಾನೆಲ್‌ನಲ್ಲಿ ಏನು ಪೋಸ್ಟ್ ಆಗುತ್ತದೆ.',
        'events' => 'ಈವೆಂಟ್‌ಗಳು',
        'events_description' => 'ಈ ವೆಬ್‌ಹುಕ್‌ಗೆ ಯಾವುದರ ಬಗ್ಗೆ ತಿಳಿಸಬೇಕು ಎಂದು ಆಯ್ಕೆಮಾಡಿ. ಕನಿಷ್ಠ ಒಂದು ಈವೆಂಟ್ ಅಗತ್ಯ.',
    ],

    'fields' => [
        'name' => 'ಹೆಸರು',
        'description' => 'ವಿವರಣೆ',
        'enabled' => 'ಸಕ್ರಿಯ',
        'endpoint' => 'ಎಂಡ್‌ಪಾಯಿಂಟ್ URL',
        'type' => 'ಪ್ರಕಾರ',
        'secret' => 'ಸಹಿ ಸೀಕ್ರೆಟ್',
        'headers' => 'ಕಸ್ಟಮ್ ಹೆಡರ್‌ಗಳು',
        'header_name' => 'ಹೆಡರ್',
        'header_value' => 'ಮೌಲ್ಯ',
        'message_username' => 'ಪ್ರದರ್ಶನ ಹೆಸರು',
        'message_avatar' => 'ಅವತಾರ್ URL',
        'message_content' => 'ಸಂದೇಶ',
        'message_title' => 'ಎಂಬೆಡ್ ಶೀರ್ಷಿಕೆ',
        'message_description' => 'ಎಂಬೆಡ್ ವಿವರಣೆ',
        'message_color' => 'ಎಂಬೆಡ್ ಬಣ್ಣ',
        'message_footer' => 'ಎಂಬೆಡ್ ಫೂಟರ್',
        'message_timestamp' => 'ಈವೆಂಟ್ ಯಾವಾಗ ನಡೆಯಿತು ಎಂದು ತೋರಿಸಿ',
    ],

    'placeholders' => [
        'name' => 'ಬಿಲ್ಲಿಂಗ್ ಸಿಂಕ್',
        'description' => 'ಈ ವೆಬ್‌ಹುಕ್ ಯಾವುದಕ್ಕಾಗಿ.',
    ],

    'helpers' => [
        'secret' => 'ಹೊಂದಿಸಿದಾಗ, ಪ್ರತಿ ವಿನಂತಿಯು X-Webhook-Signature ಹೆಡರ್ ಹೊಂದಿರುತ್ತದೆ, ಅದರಲ್ಲಿ "sha256=" ನಂತರ ಈ ಸೀಕ್ರೆಟ್‌ನಿಂದ ರಚಿಸಿದ ವಿನಂತಿ ಬಾಡಿಯ HMAC ಇರುತ್ತದೆ.',
        'message_variables' => 'ಈವೆಂಟ್ ಹೆಸರಿಗೆ {{label}}, ಅದನ್ನು ವಿವರಿಸುವ ವಾಕ್ಯಕ್ಕೆ {{summary}}, ಹಾಗೂ {{event}}, {{timestamp}}, {{panel}} ಮತ್ತು {{panel_url}} ಬಳಸಿ. ಈವೆಂಟ್‌ನಲ್ಲಿರುವ ಯಾವುದನ್ನಾದರೂ ಬಳಸಬಹುದು, ಉದಾಹರಣೆಗೆ {{data.name}} ಅಥವಾ {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'ಪ್ರಮಾಣಿತ',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'ದಾಖಲೆಗಳು',
        'auth' => 'ಸೈನ್-ಇನ್ ಚಟುವಟಿಕೆ',
        'account' => 'ಖಾತೆ ಚಟುವಟಿಕೆ',
        'server_power' => 'ಸರ್ವರ್ ಪವರ್ ಮತ್ತು ಕನ್ಸೋಲ್',
        'server_files' => 'ಸರ್ವರ್ ಫೈಲ್‌ಗಳು',
        'server_backups' => 'ಸರ್ವರ್ ಬ್ಯಾಕಪ್‌ಗಳು',
        'server_databases' => 'ಸರ್ವರ್ ಡೇಟಾಬೇಸ್‌ಗಳು',
        'server_schedules' => 'ಸರ್ವರ್ ವೇಳಾಪಟ್ಟಿಗಳು',
        'server_settings' => 'ಸರ್ವರ್ ಸೆಟ್ಟಿಂಗ್‌ಗಳು',
        'admin' => 'ಆಡಳಿತ',
    ],

    'resources' => [
        'server' => 'ಸರ್ವರ್',
        'user' => 'ಬಳಕೆದಾರ',
        'subuser' => 'ಉಪಬಳಕೆದಾರ',
        'node' => 'ನೋಡ್',
        'location' => 'ಸ್ಥಳ',
        'nest' => 'ನೆಸ್ಟ್',
        'egg' => 'ಎಗ್',
        'mount' => 'ಮೌಂಟ್',
        'database_host' => 'ಡೇಟಾಬೇಸ್ ಹೋಸ್ಟ್',
        'database' => 'ಡೇಟಾಬೇಸ್',
        'backup' => 'ಬ್ಯಾಕಪ್',
        'schedule' => 'ವೇಳಾಪಟ್ಟಿ',
        'role' => 'ಪಾತ್ರ',
        'alert' => 'ಎಚ್ಚರಿಕೆ',
    ],

    'model_actions' => [
        'created' => 'ರಚಿಸಲಾಗಿದೆ',
        'updated' => 'ನವೀಕರಿಸಲಾಗಿದೆ',
        'deleted' => 'ಅಳಿಸಲಾಗಿದೆ',
    ],

    'events' => [
        'model' => ':resource :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'ಸಿಸ್ಟಮ್',
    ],

    'columns' => [
        'name' => 'ಹೆಸರು',
        'type' => 'ಪ್ರಕಾರ',
        'endpoint' => 'ಇಲ್ಲಿಗೆ ಕಳುಹಿಸುತ್ತದೆ',
        'events' => 'ಈವೆಂಟ್‌ಗಳು',
        'last_delivery' => 'ಕೊನೆಯ ವಿತರಣೆ',
        'enabled' => 'ಸಕ್ರಿಯ',
    ],

    'statuses' => [
        'delivered' => 'ತಲುಪಿದೆ',
        'failed' => 'ವಿಫಲ',
        'never' => 'ಇನ್ನೂ ಏನನ್ನೂ ಕಳುಹಿಸಿಲ್ಲ',
    ],

    'deliveries' => [
        'title' => 'ವಿತರಣೆಗಳು',
        'label' => 'ವಿತರಣೆ',
        'plural_label' => 'ವಿತರಣೆಗಳು',
        'description' => 'ಕಳೆದ :days ದಿನಗಳಲ್ಲಿ ಈ ವೆಬ್‌ಹುಕ್‌ಗೆ ಕಳುಹಿಸಿದ ಎಲ್ಲವೂ.',
        'empty' => 'ಇನ್ನೂ ಏನನ್ನೂ ಕಳುಹಿಸಿಲ್ಲ',
        'empty_description' => 'ಆಯ್ಕೆಮಾಡಿದ ಈವೆಂಟ್‌ಗಳಲ್ಲಿ ಒಂದು ನಡೆದ ತಕ್ಷಣ ವಿತರಣೆಗಳು ಇಲ್ಲಿ ಕಾಣಿಸುತ್ತವೆ. ಈಗಲೇ ಪ್ರಯತ್ನಿಸಲು ಟೆಸ್ಟ್ ಕಳುಹಿಸಿ ಬಳಸಿ.',
        'no_response' => 'ಪ್ರತಿಕ್ರಿಯೆ ಇಲ್ಲ',
        'no_body' => 'ಎಂಡ್‌ಪಾಯಿಂಟ್ ಏನನ್ನೂ ಹಿಂತಿರುಗಿಸಲಿಲ್ಲ.',
        'disabled' => 'ಇದನ್ನು ಮತ್ತೆ ಕಳುಹಿಸಲು ವೆಬ್‌ಹುಕ್ ಸಕ್ರಿಯಗೊಳಿಸಿ.',
        'columns' => [
            'id' => 'ವಿತರಣೆ ID',
            'status' => 'ತಲುಪಿದೆ',
            'event' => 'ಈವೆಂಟ್',
            'response' => 'ಪ್ರತಿಕ್ರಿಯೆ',
            'attempts' => 'ಪ್ರಯತ್ನಗಳು',
            'duration' => 'ತೆಗೆದುಕೊಂಡ ಸಮಯ',
            'sent' => 'ಕಳುಹಿಸಲಾಗಿದೆ',
            'payload' => 'ಪೇಲೋಡ್',
            'body' => 'ಪ್ರತಿಕ್ರಿಯೆ ಬಾಡಿ',
        ],
    ],

    'actions' => [
        'test' => 'ಟೆಸ್ಟ್ ಕಳುಹಿಸಿ',
        'duplicate' => 'ನಕಲು ಮಾಡಿ',
        'redeliver' => 'ಮತ್ತೆ ಕಳುಹಿಸಿ',
        'add_header' => 'ಹೆಡರ್ ಸೇರಿಸಿ',
        'generate_secret' => 'ರಚಿಸಿ',
        'select_all' => 'ಎಲ್ಲವನ್ನೂ ಆಯ್ಕೆಮಾಡಿ',
        'deselect_all' => 'ಎಲ್ಲದರ ಆಯ್ಕೆ ತೆಗೆಯಿರಿ',
    ],

    'notices' => [
        'delivered' => 'ಟೆಸ್ಟ್ ತಲುಪಿದೆ.',
        'failed' => 'ಟೆಸ್ಟ್ ತಲುಪಿಸಲು ಸಾಧ್ಯವಾಗಲಿಲ್ಲ.',
        'responded' => 'ಎಂಡ್‌ಪಾಯಿಂಟ್ :ms ms ನಲ್ಲಿ :status ಉತ್ತರ ನೀಡಿದೆ.',
        'duplicated' => 'ವೆಬ್‌ಹುಕ್ ಅನ್ನು ನಕಲು ಮಾಡಿ ನಿಷ್ಕ್ರಿಯವಾಗಿ ಇಡಲಾಗಿದೆ.',
        'redelivery_queued' => 'ಈವೆಂಟ್ ಮತ್ತೆ ಕಳುಹಿಸಲಾಗುತ್ತಿದೆ.',
    ],

    'validation' => [
        'events' => 'ಕನಿಷ್ಠ ಒಂದು ಈವೆಂಟ್ ಆಯ್ಕೆಮಾಡಿ.',
        'header_name' => '":name" ಮಾನ್ಯ ಹೆಡರ್ ಹೆಸರಲ್ಲ.',
        'header_reserved' => '":name" ಹೆಡರ್ ಅನ್ನು ಪ್ಯಾನೆಲ್ ಹೊಂದಿಸುತ್ತದೆ ಮತ್ತು ಬದಲಾಯಿಸಲು ಸಾಧ್ಯವಿಲ್ಲ.',
    ],

    'errors' => [
        'invalid_endpoint' => 'ಎಂಡ್‌ಪಾಯಿಂಟ್ ಮಾನ್ಯ http ಅಥವಾ https ವಿಳಾಸವಲ್ಲ.',
        'unresolvable' => ':host ನ ವಿಳಾಸ ಸಿಗಲಿಲ್ಲ.',
        'private_endpoint' => ':host ಖಾಸಗಿ ಅಥವಾ ಕಾಯ್ದಿರಿಸಿದ ವಿಳಾಸವನ್ನು ಸೂಚಿಸುತ್ತದೆ. ನಿಮ್ಮ ಸ್ವಂತ ನೆಟ್‌ವರ್ಕ್ ಒಳಗೆ ವಿತರಣೆಗೆ ಅನುಮತಿಸಲು APP_WEBHOOK_ALLOW_PRIVATE=true ಹೊಂದಿಸಿ.',
        'unreachable' => ':host ಗೆ ಸಂಪರ್ಕಿಸಲು ಸಾಧ್ಯವಾಗಲಿಲ್ಲ.',
    ],

    'copy_suffix' => ':name (ನಕಲು)',

];
