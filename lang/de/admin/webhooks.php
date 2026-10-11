<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhooks',

    'sections' => [
        'details' => 'Details',
        'details_description' => 'Webhooks informieren einen anderen Dienst in dem Moment, in dem im Panel etwas passiert.',
        'delivery' => 'Zustellung',
        'delivery_description' => 'Wohin Ereignisse gesendet werden und wie die Gegenseite ihnen vertrauen kann.',
        'message' => 'Discord-Nachricht',
        'message_description' => 'Was für jedes Ereignis im Kanal gepostet wird.',
        'events' => 'Ereignisse',
        'events_description' => 'Wähle aus, worüber dieser Webhook informiert wird. Mindestens ein Ereignis ist erforderlich.',
    ],

    'fields' => [
        'name' => 'Name',
        'description' => 'Beschreibung',
        'enabled' => 'Aktiviert',
        'endpoint' => 'Endpunkt-URL',
        'type' => 'Typ',
        'secret' => 'Signaturschlüssel',
        'headers' => 'Eigene Header',
        'header_name' => 'Header',
        'header_value' => 'Wert',
        'message_username' => 'Anzeigename',
        'message_avatar' => 'Avatar-URL',
        'message_content' => 'Nachricht',
        'message_title' => 'Embed-Titel',
        'message_description' => 'Embed-Beschreibung',
        'message_color' => 'Embed-Farbe',
        'message_footer' => 'Embed-Fußzeile',
        'message_timestamp' => 'Zeitpunkt des Ereignisses anzeigen',
    ],

    'placeholders' => [
        'name' => 'Abrechnungsabgleich',
        'description' => 'Wofür dieser Webhook gedacht ist.',
    ],

    'helpers' => [
        'secret' => 'Wenn gesetzt, enthält jede Anfrage einen X-Webhook-Signature-Header mit "sha256=" gefolgt vom HMAC des Anfrageinhalts, der mit diesem Schlüssel erzeugt wurde.',
        'message_variables' => 'Verwende {{label}} für den Namen des Ereignisses, {{summary}} für einen Satz, der es beschreibt, sowie {{event}}, {{timestamp}}, {{panel}} und {{panel_url}}. Alles aus dem Ereignis kann ebenfalls verwendet werden, zum Beispiel {{data.name}} oder {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Standard',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Datensätze',
        'auth' => 'Anmeldeaktivität',
        'account' => 'Kontoaktivität',
        'server_power' => 'Server-Energie und Konsole',
        'server_files' => 'Server-Dateien',
        'server_backups' => 'Server-Backups',
        'server_databases' => 'Server-Datenbanken',
        'server_schedules' => 'Server-Zeitpläne',
        'server_settings' => 'Server-Einstellungen',
        'admin' => 'Administration',
    ],

    'resources' => [
        'server' => 'Server',
        'user' => 'Benutzer',
        'subuser' => 'Unterbenutzer',
        'node' => 'Knoten',
        'location' => 'Standort',
        'nest' => 'Nest',
        'egg' => 'Egg',
        'mount' => 'Mount',
        'database_host' => 'Datenbank-Host',
        'database' => 'Datenbank',
        'backup' => 'Backup',
        'schedule' => 'Zeitplan',
        'role' => 'Rolle',
        'alert' => 'Hinweis',
    ],

    'model_actions' => [
        'created' => 'erstellt',
        'updated' => 'aktualisiert',
        'deleted' => 'gelöscht',
    ],

    'events' => [
        'model' => ':resource :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'System',
    ],

    'columns' => [
        'name' => 'Name',
        'type' => 'Typ',
        'endpoint' => 'Sendet an',
        'events' => 'Ereignisse',
        'last_delivery' => 'Letzte Zustellung',
        'enabled' => 'Aktiviert',
    ],

    'statuses' => [
        'delivered' => 'Zugestellt',
        'failed' => 'Fehlgeschlagen',
        'never' => 'Noch nichts gesendet',
    ],

    'deliveries' => [
        'title' => 'Zustellungen',
        'label' => 'Zustellung',
        'plural_label' => 'Zustellungen',
        'description' => 'Alles, was in den letzten :days Tagen an diesen Webhook gesendet wurde.',
        'empty' => 'Noch nichts gesendet',
        'empty_description' => 'Zustellungen erscheinen hier, sobald eines der gewählten Ereignisse eintritt. Mit „Test senden“ kannst du es sofort ausprobieren.',
        'no_response' => 'Keine Antwort',
        'no_body' => 'Der Endpunkt hat nichts zurückgesendet.',
        'disabled' => 'Aktiviere den Webhook, um dies erneut zu senden.',
        'columns' => [
            'id' => 'Zustellungs-ID',
            'status' => 'Zugestellt',
            'event' => 'Ereignis',
            'response' => 'Antwort',
            'attempts' => 'Versuche',
            'duration' => 'Dauer',
            'sent' => 'Gesendet',
            'payload' => 'Nutzdaten',
            'body' => 'Antwortinhalt',
        ],
    ],

    'actions' => [
        'test' => 'Test senden',
        'duplicate' => 'Duplizieren',
        'redeliver' => 'Erneut senden',
        'add_header' => 'Header hinzufügen',
        'generate_secret' => 'Generieren',
        'select_all' => 'Alle auswählen',
        'deselect_all' => 'Alle abwählen',
    ],

    'notices' => [
        'delivered' => 'Der Test wurde zugestellt.',
        'failed' => 'Der Test konnte nicht zugestellt werden.',
        'responded' => 'Der Endpunkt antwortete mit :status in :ms ms.',
        'duplicated' => 'Der Webhook wurde dupliziert und bleibt deaktiviert.',
        'redelivery_queued' => 'Das Ereignis ist erneut unterwegs.',
    ],

    'validation' => [
        'events' => 'Wähle mindestens ein Ereignis aus.',
        'header_name' => '":name" ist kein gültiger Header-Name.',
        'header_reserved' => 'Der Header ":name" wird vom Panel gesetzt und kann nicht geändert werden.',
    ],

    'errors' => [
        'invalid_endpoint' => 'Der Endpunkt ist keine gültige http- oder https-Adresse.',
        'unresolvable' => 'Die Adresse für :host konnte nicht gefunden werden.',
        'private_endpoint' => ':host verweist auf eine private oder reservierte Adresse. Setze APP_WEBHOOK_ALLOW_PRIVATE=true, um Zustellungen innerhalb deines eigenen Netzwerks zu erlauben.',
        'unreachable' => 'Verbindung zu :host konnte nicht hergestellt werden.',
    ],

    'copy_suffix' => ':name (Kopie)',

];
