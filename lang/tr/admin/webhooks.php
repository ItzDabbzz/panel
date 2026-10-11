<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhook\'lar',

    'sections' => [
        'details' => 'Ayrıntılar',
        'details_description' => 'Webhook\'lar, panelde bir şey olduğu anda başka bir servise haber verir.',
        'delivery' => 'Teslimat',
        'delivery_description' => 'Olayların nereye gönderildiği ve alıcının onlara nasıl güvenebileceği.',
        'message' => 'Discord Mesajı',
        'message_description' => 'Her olay için kanala gönderilen içerik.',
        'events' => 'Olaylar',
        'events_description' => 'Bu webhook\'un nelerden haberdar edileceğini seçin. En az bir olay gereklidir.',
    ],

    'fields' => [
        'name' => 'Ad',
        'description' => 'Açıklama',
        'enabled' => 'Etkin',
        'endpoint' => 'Uç Nokta URL\'si',
        'type' => 'Tür',
        'secret' => 'İmzalama Anahtarı',
        'headers' => 'Özel Başlıklar',
        'header_name' => 'Başlık',
        'header_value' => 'Değer',
        'message_username' => 'Görünen Ad',
        'message_avatar' => 'Avatar URL\'si',
        'message_content' => 'Mesaj',
        'message_title' => 'Embed Başlığı',
        'message_description' => 'Embed Açıklaması',
        'message_color' => 'Embed Rengi',
        'message_footer' => 'Embed Alt Bilgisi',
        'message_timestamp' => 'Olayın ne zaman gerçekleştiğini göster',
    ],

    'placeholders' => [
        'name' => 'Fatura eşitleme',
        'description' => 'Bu webhook\'un ne işe yaradığı.',
    ],

    'helpers' => [
        'secret' => 'Ayarlandığında her istek, "sha256=" ve ardından istek gövdesinin bu anahtarla üretilmiş HMAC değerini içeren bir X-Webhook-Signature başlığı taşır.',
        'message_variables' => 'Olayın adı için {{label}}, onu anlatan bir cümle için {{summary}}, ayrıca {{event}}, {{timestamp}}, {{panel}} ve {{panel_url}} kullanın. Olaydaki her şey de kullanılabilir, örneğin {{data.name}} veya {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Standart',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Kayıtlar',
        'auth' => 'Oturum Açma Etkinliği',
        'account' => 'Hesap Etkinliği',
        'server_power' => 'Sunucu Gücü ve Konsol',
        'server_files' => 'Sunucu Dosyaları',
        'server_backups' => 'Sunucu Yedekleri',
        'server_databases' => 'Sunucu Veritabanları',
        'server_schedules' => 'Sunucu Zamanlamaları',
        'server_settings' => 'Sunucu Ayarları',
        'admin' => 'Yönetim',
    ],

    'resources' => [
        'server' => 'Sunucu',
        'user' => 'Kullanıcı',
        'subuser' => 'Alt kullanıcı',
        'node' => 'Düğüm',
        'location' => 'Konum',
        'nest' => 'Nest',
        'egg' => 'Egg',
        'mount' => 'Bağlama Noktası',
        'database_host' => 'Veritabanı sunucusu',
        'database' => 'Veritabanı',
        'backup' => 'Yedek',
        'schedule' => 'Zamanlama',
        'role' => 'Rol',
        'alert' => 'Uyarı',
    ],

    'model_actions' => [
        'created' => 'oluşturuldu',
        'updated' => 'güncellendi',
        'deleted' => 'silindi',
    ],

    'events' => [
        'model' => ':resource :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'Sistem',
    ],

    'columns' => [
        'name' => 'Ad',
        'type' => 'Tür',
        'endpoint' => 'Gönderim Adresi',
        'events' => 'Olaylar',
        'last_delivery' => 'Son Teslimat',
        'enabled' => 'Etkin',
    ],

    'statuses' => [
        'delivered' => 'Teslim edildi',
        'failed' => 'Başarısız',
        'never' => 'Henüz bir şey gönderilmedi',
    ],

    'deliveries' => [
        'title' => 'Teslimatlar',
        'label' => 'teslimat',
        'plural_label' => 'teslimatlar',
        'description' => 'Son :days gün içinde bu webhook\'a gönderilen her şey.',
        'empty' => 'Henüz bir şey gönderilmedi',
        'empty_description' => 'Seçilen olaylardan biri gerçekleştiğinde teslimatlar burada görünür. Şimdi denemek için Test Gönder\'i kullanın.',
        'no_response' => 'Yanıt yok',
        'no_body' => 'Uç nokta hiçbir şey döndürmedi.',
        'disabled' => 'Bunu yeniden göndermek için webhook\'u etkinleştirin.',
        'columns' => [
            'id' => 'Teslimat Kimliği',
            'status' => 'Teslim edildi',
            'event' => 'Olay',
            'response' => 'Yanıt',
            'attempts' => 'Deneme',
            'duration' => 'Süre',
            'sent' => 'Gönderildi',
            'payload' => 'İçerik',
            'body' => 'Yanıt Gövdesi',
        ],
    ],

    'actions' => [
        'test' => 'Test Gönder',
        'duplicate' => 'Çoğalt',
        'redeliver' => 'Yeniden Gönder',
        'add_header' => 'Başlık Ekle',
        'generate_secret' => 'Oluştur',
        'select_all' => 'Tümünü seç',
        'deselect_all' => 'Tüm seçimi kaldır',
    ],

    'notices' => [
        'delivered' => 'Test teslim edildi.',
        'failed' => 'Test teslim edilemedi.',
        'responded' => 'Uç nokta :ms ms içinde :status yanıtını verdi.',
        'duplicated' => 'Webhook çoğaltıldı ve devre dışı bırakıldı.',
        'redelivery_queued' => 'Olay yeniden yola çıktı.',
    ],

    'validation' => [
        'events' => 'En az bir olay seçin.',
        'header_name' => '":name" geçerli bir başlık adı değil.',
        'header_reserved' => '":name" başlığı panel tarafından ayarlanır ve değiştirilemez.',
    ],

    'errors' => [
        'invalid_endpoint' => 'Uç nokta geçerli bir http veya https adresi değil.',
        'unresolvable' => ':host için adres bulunamadı.',
        'private_endpoint' => ':host özel veya ayrılmış bir adrese işaret ediyor. Kendi ağınız içindeki teslimatlara izin vermek için APP_WEBHOOK_ALLOW_PRIVATE=true ayarlayın.',
        'unreachable' => ':host adresine bağlanılamadı.',
    ],

    'copy_suffix' => ':name (kopya)',

];
