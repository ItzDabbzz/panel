<?php

return [

    'label' => 'ওয়েবহুক',
    'plural_label' => 'ওয়েবহুকসমূহ',

    'sections' => [
        'details' => 'বিস্তারিত',
        'details_description' => 'প্যানেলে কিছু ঘটার মুহূর্তেই ওয়েবহুক অন্য একটি সার্ভিসকে জানিয়ে দেয়।',
        'delivery' => 'ডেলিভারি',
        'delivery_description' => 'ইভেন্ট কোথায় পাঠানো হয় এবং প্রাপক কীভাবে সেগুলোকে বিশ্বাস করতে পারে।',
        'message' => 'Discord বার্তা',
        'message_description' => 'প্রতিটি ইভেন্টের জন্য চ্যানেলে যা পোস্ট করা হয়।',
        'events' => 'ইভেন্ট',
        'events_description' => 'এই ওয়েবহুককে কী কী জানানো হবে তা বেছে নিন। অন্তত একটি ইভেন্ট প্রয়োজন।',
    ],

    'fields' => [
        'name' => 'নাম',
        'description' => 'বিবরণ',
        'enabled' => 'সক্রিয়',
        'endpoint' => 'এন্ডপয়েন্ট URL',
        'type' => 'ধরন',
        'secret' => 'সাইনিং সিক্রেট',
        'headers' => 'কাস্টম হেডার',
        'header_name' => 'হেডার',
        'header_value' => 'মান',
        'message_username' => 'প্রদর্শিত নাম',
        'message_avatar' => 'অ্যাভাটার URL',
        'message_content' => 'বার্তা',
        'message_title' => 'এম্বেড শিরোনাম',
        'message_description' => 'এম্বেড বিবরণ',
        'message_color' => 'এম্বেড রং',
        'message_footer' => 'এম্বেড ফুটার',
        'message_timestamp' => 'ইভেন্ট কখন ঘটেছে তা দেখান',
    ],

    'placeholders' => [
        'name' => 'বিলিং সিঙ্ক',
        'description' => 'এই ওয়েবহুক কী কাজে লাগে।',
    ],

    'helpers' => [
        'secret' => 'সেট করা থাকলে প্রতিটি অনুরোধে একটি X-Webhook-Signature হেডার থাকে, যাতে "sha256=" এর পরে এই সিক্রেট দিয়ে তৈরি অনুরোধ বডির HMAC থাকে।',
        'message_variables' => 'ইভেন্টের নামের জন্য {{label}}, সেটি বর্ণনা করা একটি বাক্যের জন্য {{summary}}, এবং {{event}}, {{timestamp}}, {{panel}} ও {{panel_url}} ব্যবহার করুন। ইভেন্টের যেকোনো তথ্যও ব্যবহার করা যায়, যেমন {{data.name}} বা {{data.actor.name}}।',
    ],

    'types' => [
        'standard' => 'স্ট্যান্ডার্ড',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'রেকর্ড',
        'auth' => 'সাইন-ইন কার্যকলাপ',
        'account' => 'অ্যাকাউন্ট কার্যকলাপ',
        'server_power' => 'সার্ভার পাওয়ার ও কনসোল',
        'server_files' => 'সার্ভার ফাইল',
        'server_backups' => 'সার্ভার ব্যাকআপ',
        'server_databases' => 'সার্ভার ডাটাবেস',
        'server_schedules' => 'সার্ভার শিডিউল',
        'server_settings' => 'সার্ভার সেটিংস',
        'admin' => 'প্রশাসন',
    ],

    'resources' => [
        'server' => 'সার্ভার',
        'user' => 'ইউজার',
        'subuser' => 'সাব-ইউজার',
        'node' => 'নোড',
        'location' => 'লোকেশন',
        'nest' => 'নেস্ট',
        'egg' => 'এগ',
        'mount' => 'মাউন্ট',
        'database_host' => 'ডাটাবেস হোস্ট',
        'database' => 'ডাটাবেস',
        'backup' => 'ব্যাকআপ',
        'schedule' => 'শিডিউল',
        'role' => 'রোল',
        'alert' => 'সতর্কবার্তা',
    ],

    'model_actions' => [
        'created' => 'তৈরি হয়েছে',
        'updated' => 'আপডেট হয়েছে',
        'deleted' => 'মুছে ফেলা হয়েছে',
    ],

    'events' => [
        'model' => ':resource :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'সিস্টেম',
    ],

    'columns' => [
        'name' => 'নাম',
        'type' => 'ধরন',
        'endpoint' => 'যেখানে পাঠায়',
        'events' => 'ইভেন্ট',
        'last_delivery' => 'সর্বশেষ ডেলিভারি',
        'enabled' => 'সক্রিয়',
    ],

    'statuses' => [
        'delivered' => 'ডেলিভার হয়েছে',
        'failed' => 'ব্যর্থ',
        'never' => 'এখনো কিছু পাঠানো হয়নি',
    ],

    'deliveries' => [
        'title' => 'ডেলিভারিসমূহ',
        'label' => 'ডেলিভারি',
        'plural_label' => 'ডেলিভারি',
        'description' => 'গত :days দিনে এই ওয়েবহুকে যা কিছু পাঠানো হয়েছে।',
        'empty' => 'এখনো কিছু পাঠানো হয়নি',
        'empty_description' => 'নির্বাচিত ইভেন্টগুলোর কোনো একটি ঘটলেই ডেলিভারি এখানে দেখা যায়। এখনই পরীক্ষা করতে টেস্ট পাঠান ব্যবহার করুন।',
        'no_response' => 'কোনো সাড়া নেই',
        'no_body' => 'এন্ডপয়েন্ট কিছুই ফেরত পাঠায়নি।',
        'disabled' => 'এটি আবার পাঠাতে ওয়েবহুক সক্রিয় করুন।',
        'columns' => [
            'id' => 'ডেলিভারি ID',
            'status' => 'ডেলিভার হয়েছে',
            'event' => 'ইভেন্ট',
            'response' => 'সাড়া',
            'attempts' => 'চেষ্টা',
            'duration' => 'সময় লেগেছে',
            'sent' => 'পাঠানো হয়েছে',
            'payload' => 'পেলোড',
            'body' => 'সাড়ার বডি',
        ],
    ],

    'actions' => [
        'test' => 'টেস্ট পাঠান',
        'duplicate' => 'ডুপ্লিকেট',
        'redeliver' => 'আবার পাঠান',
        'add_header' => 'হেডার যোগ করুন',
        'generate_secret' => 'তৈরি করুন',
        'select_all' => 'সব নির্বাচন করুন',
        'deselect_all' => 'সব নির্বাচন বাতিল করুন',
    ],

    'notices' => [
        'delivered' => 'টেস্ট ডেলিভার হয়েছে।',
        'failed' => 'টেস্ট ডেলিভার করা যায়নি।',
        'responded' => 'এন্ডপয়েন্ট :ms ms-এ :status উত্তর দিয়েছে।',
        'duplicated' => 'ওয়েবহুকটি ডুপ্লিকেট করা হয়েছে এবং নিষ্ক্রিয় রাখা হয়েছে।',
        'redelivery_queued' => 'ইভেন্টটি আবার পাঠানো হচ্ছে।',
    ],

    'validation' => [
        'events' => 'অন্তত একটি ইভেন্ট বেছে নিন।',
        'header_name' => '":name" বৈধ হেডারের নাম নয়।',
        'header_reserved' => '":name" হেডারটি প্যানেল নিজে সেট করে এবং পরিবর্তন করা যায় না।',
    ],

    'errors' => [
        'invalid_endpoint' => 'এন্ডপয়েন্টটি বৈধ http বা https ঠিকানা নয়।',
        'unresolvable' => ':host এর ঠিকানা খুঁজে পাওয়া যায়নি।',
        'private_endpoint' => ':host একটি প্রাইভেট বা সংরক্ষিত ঠিকানা নির্দেশ করে। নিজের নেটওয়ার্কের ভেতরে ডেলিভারির অনুমতি দিতে APP_WEBHOOK_ALLOW_PRIVATE=true সেট করুন।',
        'unreachable' => ':host এর সাথে সংযোগ করা যায়নি।',
        'curl_required' => 'হোস্টনেমে ডেলিভারির জন্য PHP cURL এক্সটেনশন প্রয়োজন, যা ইনস্টল করা নেই।',
    ],

    'copy_suffix' => ':name (কপি)',

];
