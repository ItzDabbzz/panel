<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhooks',

    'sections' => [
        'details' => 'Detaljer',
        'details_description' => 'Webhooks meddelar en annan tjänst i samma stund som något händer i panelen.',
        'delivery' => 'Leverans',
        'delivery_description' => 'Vart händelser skickas och hur mottagaren kan lita på dem.',
        'message' => 'Discord-meddelande',
        'message_description' => 'Vad som publiceras i kanalen för varje händelse.',
        'events' => 'Händelser',
        'events_description' => 'Välj vad denna webhook får veta om. Minst en händelse krävs.',
    ],

    'fields' => [
        'name' => 'Namn',
        'description' => 'Beskrivning',
        'enabled' => 'Aktiverad',
        'endpoint' => 'Slutpunkts-URL',
        'type' => 'Typ',
        'secret' => 'Signeringshemlighet',
        'headers' => 'Egna headers',
        'header_name' => 'Header',
        'header_value' => 'Värde',
        'message_username' => 'Visningsnamn',
        'message_avatar' => 'Avatar-URL',
        'message_content' => 'Meddelande',
        'message_title' => 'Embed-titel',
        'message_description' => 'Embed-beskrivning',
        'message_color' => 'Embed-färg',
        'message_footer' => 'Embed-sidfot',
        'message_timestamp' => 'Visa när händelsen inträffade',
    ],

    'placeholders' => [
        'name' => 'Faktureringssynk',
        'description' => 'Vad denna webhook är till för.',
    ],

    'helpers' => [
        'secret' => 'När den är satt har varje förfrågan en X-Webhook-Signature-header med "sha256=" följt av HMAC för förfrågans innehåll, skapad med denna hemlighet.',
        'message_variables' => 'Använd {{label}} för händelsens namn, {{summary}} för en mening som beskriver den, {{event}}, {{timestamp}}, {{panel}} och {{panel_url}}. Allt i händelsen kan också användas, till exempel {{data.name}} eller {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Standard',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Poster',
        'auth' => 'Inloggningsaktivitet',
        'account' => 'Kontoaktivitet',
        'server_power' => 'Serverns ström och konsol',
        'server_files' => 'Serverfiler',
        'server_backups' => 'Serverbackuper',
        'server_databases' => 'Serverdatabaser',
        'server_schedules' => 'Serverscheman',
        'server_settings' => 'Serverinställningar',
        'admin' => 'Administration',
    ],

    'resources' => [
        'server' => 'Server',
        'user' => 'Användare',
        'subuser' => 'Underanvändare',
        'node' => 'Nod',
        'location' => 'Plats',
        'nest' => 'Bur',
        'egg' => 'Egg',
        'mount' => 'Montering',
        'database_host' => 'Databasvärd',
        'database' => 'Databas',
        'backup' => 'Backup',
        'schedule' => 'Schema',
        'role' => 'Roll',
        'alert' => 'Avisering',
    ],

    'model_actions' => [
        'created' => 'skapades',
        'updated' => 'uppdaterades',
        'deleted' => 'raderades',
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
        'name' => 'Namn',
        'type' => 'Typ',
        'endpoint' => 'Skickar till',
        'events' => 'Händelser',
        'last_delivery' => 'Senaste leverans',
        'enabled' => 'Aktiverad',
    ],

    'statuses' => [
        'delivered' => 'Levererad',
        'failed' => 'Misslyckades',
        'never' => 'Inget har skickats ännu',
    ],

    'deliveries' => [
        'title' => 'Leveranser',
        'label' => 'leverans',
        'plural_label' => 'leveranser',
        'description' => 'Allt som skickats till denna webhook de senaste :days dagarna.',
        'empty' => 'Inget har skickats ännu',
        'empty_description' => 'Leveranser visas här så snart en av de valda händelserna inträffar. Använd Skicka test för att prova nu.',
        'no_response' => 'Inget svar',
        'no_body' => 'Slutpunkten skickade ingenting tillbaka.',
        'disabled' => 'Aktivera webhooken för att skicka detta igen.',
        'columns' => [
            'id' => 'Leverans-ID',
            'status' => 'Levererad',
            'event' => 'Händelse',
            'response' => 'Svar',
            'attempts' => 'Försök',
            'duration' => 'Tid',
            'sent' => 'Skickad',
            'payload' => 'Innehåll',
            'body' => 'Svarsinnehåll',
        ],
    ],

    'actions' => [
        'test' => 'Skicka test',
        'duplicate' => 'Duplicera',
        'redeliver' => 'Skicka igen',
        'add_header' => 'Lägg till header',
        'generate_secret' => 'Generera',
        'select_all' => 'Markera alla',
        'deselect_all' => 'Avmarkera alla',
    ],

    'notices' => [
        'delivered' => 'Testet levererades.',
        'failed' => 'Testet kunde inte levereras.',
        'responded' => 'Slutpunkten svarade :status på :ms ms.',
        'duplicated' => 'Webhooken duplicerades och lämnades inaktiverad.',
        'redelivery_queued' => 'Händelsen är på väg igen.',
    ],

    'validation' => [
        'events' => 'Välj minst en händelse.',
        'header_name' => '":name" är inte ett giltigt headernamn.',
        'header_reserved' => 'Headern ":name" sätts av panelen och kan inte ändras.',
    ],

    'errors' => [
        'invalid_endpoint' => 'Slutpunkten är inte en giltig http- eller https-adress.',
        'unresolvable' => 'Adressen för :host kunde inte hittas.',
        'private_endpoint' => ':host pekar på en privat eller reserverad adress. Sätt APP_WEBHOOK_ALLOW_PRIVATE=true för att tillåta leveranser inom ditt eget nätverk.',
        'unreachable' => 'Kunde inte ansluta till :host.',
    ],

    'copy_suffix' => ':name (kopia)',

];
