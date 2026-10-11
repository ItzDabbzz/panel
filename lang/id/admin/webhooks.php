<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhook',

    'sections' => [
        'details' => 'Detail',
        'details_description' => 'Webhook memberi tahu layanan lain saat sesuatu terjadi di panel.',
        'delivery' => 'Pengiriman',
        'delivery_description' => 'Ke mana event dikirim dan bagaimana penerima dapat memercayainya.',
        'message' => 'Pesan Discord',
        'message_description' => 'Apa yang diposting ke kanal untuk setiap event.',
        'events' => 'Event',
        'events_description' => 'Pilih apa saja yang diberitahukan ke webhook ini. Minimal satu event diperlukan.',
    ],

    'fields' => [
        'name' => 'Nama',
        'description' => 'Deskripsi',
        'enabled' => 'Aktif',
        'endpoint' => 'URL Endpoint',
        'type' => 'Tipe',
        'secret' => 'Kunci Penandatanganan',
        'headers' => 'Header Kustom',
        'header_name' => 'Header',
        'header_value' => 'Nilai',
        'message_username' => 'Nama Tampilan',
        'message_avatar' => 'URL Avatar',
        'message_content' => 'Pesan',
        'message_title' => 'Judul Embed',
        'message_description' => 'Deskripsi Embed',
        'message_color' => 'Warna Embed',
        'message_footer' => 'Footer Embed',
        'message_timestamp' => 'Tampilkan kapan event terjadi',
    ],

    'placeholders' => [
        'name' => 'Sinkronisasi tagihan',
        'description' => 'Untuk apa webhook ini.',
    ],

    'helpers' => [
        'secret' => 'Jika diisi, setiap permintaan membawa header X-Webhook-Signature berisi "sha256=" diikuti HMAC dari isi permintaan yang dibuat dengan kunci ini.',
        'message_variables' => 'Gunakan {{label}} untuk nama event, {{summary}} untuk kalimat yang menjelaskannya, {{event}}, {{timestamp}}, {{panel}}, dan {{panel_url}}. Semua isi event juga bisa dipakai, misalnya {{data.name}} atau {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Standar',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Data',
        'auth' => 'Aktivitas Masuk',
        'account' => 'Aktivitas Akun',
        'server_power' => 'Daya dan Konsol Server',
        'server_files' => 'File Server',
        'server_backups' => 'Cadangan Server',
        'server_databases' => 'Database Server',
        'server_schedules' => 'Jadwal Server',
        'server_settings' => 'Pengaturan Server',
        'admin' => 'Administrasi',
    ],

    'resources' => [
        'server' => 'Server',
        'user' => 'Pengguna',
        'subuser' => 'Subpengguna',
        'node' => 'Node',
        'location' => 'Lokasi',
        'nest' => 'Nest',
        'egg' => 'Egg',
        'mount' => 'Mount',
        'database_host' => 'Host database',
        'database' => 'Database',
        'backup' => 'Cadangan',
        'schedule' => 'Jadwal',
        'role' => 'Peran',
        'alert' => 'Peringatan',
    ],

    'model_actions' => [
        'created' => 'dibuat',
        'updated' => 'diperbarui',
        'deleted' => 'dihapus',
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
        'name' => 'Nama',
        'type' => 'Tipe',
        'endpoint' => 'Dikirim Ke',
        'events' => 'Event',
        'last_delivery' => 'Pengiriman Terakhir',
        'enabled' => 'Aktif',
    ],

    'statuses' => [
        'delivered' => 'Terkirim',
        'failed' => 'Gagal',
        'never' => 'Belum ada yang dikirim',
    ],

    'deliveries' => [
        'title' => 'Pengiriman',
        'label' => 'pengiriman',
        'plural_label' => 'pengiriman',
        'description' => 'Semua yang dikirim ke webhook ini dalam :days hari terakhir.',
        'empty' => 'Belum ada yang dikirim',
        'empty_description' => 'Pengiriman muncul di sini begitu salah satu event yang dipilih terjadi. Gunakan Kirim Tes untuk mencobanya sekarang.',
        'no_response' => 'Tidak ada respons',
        'no_body' => 'Endpoint tidak mengirim balasan apa pun.',
        'disabled' => 'Aktifkan webhook untuk mengirim ini lagi.',
        'columns' => [
            'id' => 'ID Pengiriman',
            'status' => 'Terkirim',
            'event' => 'Event',
            'response' => 'Respons',
            'attempts' => 'Percobaan',
            'duration' => 'Durasi',
            'sent' => 'Dikirim',
            'payload' => 'Isi',
            'body' => 'Isi Respons',
        ],
    ],

    'actions' => [
        'test' => 'Kirim Tes',
        'duplicate' => 'Duplikat',
        'redeliver' => 'Kirim Lagi',
        'add_header' => 'Tambah Header',
        'generate_secret' => 'Buat',
        'select_all' => 'Pilih semua',
        'deselect_all' => 'Batalkan semua pilihan',
    ],

    'notices' => [
        'delivered' => 'Tes berhasil dikirim.',
        'failed' => 'Tes tidak dapat dikirim.',
        'responded' => 'Endpoint menjawab :status dalam :ms ms.',
        'duplicated' => 'Webhook diduplikat dan dibiarkan nonaktif.',
        'redelivery_queued' => 'Event sedang dikirim lagi.',
    ],

    'validation' => [
        'events' => 'Pilih minimal satu event.',
        'header_name' => '":name" bukan nama header yang valid.',
        'header_reserved' => 'Header ":name" diatur oleh panel dan tidak dapat diubah.',
    ],

    'errors' => [
        'invalid_endpoint' => 'Endpoint bukan alamat http atau https yang valid.',
        'unresolvable' => 'Alamat untuk :host tidak ditemukan.',
        'private_endpoint' => ':host mengarah ke alamat privat atau khusus. Atur APP_WEBHOOK_ALLOW_PRIVATE=true untuk mengizinkan pengiriman di dalam jaringan Anda sendiri.',
        'unreachable' => 'Tidak dapat terhubung ke :host.',
    ],

    'copy_suffix' => ':name (salinan)',

];
