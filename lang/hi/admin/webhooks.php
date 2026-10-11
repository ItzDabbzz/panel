<?php

return [

    'label' => 'वेबहुक',
    'plural_label' => 'वेबहुक',

    'sections' => [
        'details' => 'विवरण',
        'details_description' => 'पैनल पर कुछ होते ही वेबहुक किसी दूसरी सेवा को सूचित करते हैं।',
        'delivery' => 'डिलीवरी',
        'delivery_description' => 'इवेंट कहाँ भेजे जाते हैं और प्राप्त करने वाला उन पर कैसे भरोसा कर सकता है।',
        'message' => 'Discord संदेश',
        'message_description' => 'हर इवेंट के लिए चैनल में क्या पोस्ट होता है।',
        'events' => 'इवेंट',
        'events_description' => 'चुनें कि इस वेबहुक को किस बारे में बताया जाए। कम से कम एक इवेंट ज़रूरी है।',
    ],

    'fields' => [
        'name' => 'नाम',
        'description' => 'विवरण',
        'enabled' => 'सक्षम',
        'endpoint' => 'एंडपॉइंट URL',
        'type' => 'प्रकार',
        'secret' => 'हस्ताक्षर सीक्रेट',
        'headers' => 'कस्टम हेडर',
        'header_name' => 'हेडर',
        'header_value' => 'मान',
        'message_username' => 'प्रदर्शित नाम',
        'message_avatar' => 'अवतार URL',
        'message_content' => 'संदेश',
        'message_title' => 'एम्बेड शीर्षक',
        'message_description' => 'एम्बेड विवरण',
        'message_color' => 'एम्बेड रंग',
        'message_footer' => 'एम्बेड फ़ुटर',
        'message_timestamp' => 'दिखाएँ कि इवेंट कब हुआ',
    ],

    'placeholders' => [
        'name' => 'बिलिंग सिंक',
        'description' => 'यह वेबहुक किस लिए है।',
    ],

    'helpers' => [
        'secret' => 'सेट होने पर हर अनुरोध में X-Webhook-Signature हेडर होता है, जिसमें "sha256=" के बाद इस सीक्रेट से बना अनुरोध बॉडी का HMAC होता है।',
        'message_variables' => 'इवेंट के नाम के लिए {{label}}, उसका वर्णन करने वाले वाक्य के लिए {{summary}}, और {{event}}, {{timestamp}}, {{panel}} तथा {{panel_url}} का उपयोग करें। इवेंट की कोई भी जानकारी भी इस्तेमाल की जा सकती है, जैसे {{data.name}} या {{data.actor.name}}।',
    ],

    'types' => [
        'standard' => 'मानक',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'रिकॉर्ड',
        'auth' => 'साइन-इन गतिविधि',
        'account' => 'खाता गतिविधि',
        'server_power' => 'सर्वर पावर और कंसोल',
        'server_files' => 'सर्वर फ़ाइलें',
        'server_backups' => 'सर्वर बैकअप',
        'server_databases' => 'सर्वर डेटाबेस',
        'server_schedules' => 'सर्वर शेड्यूल',
        'server_settings' => 'सर्वर सेटिंग्स',
        'admin' => 'प्रशासन',
    ],

    'resources' => [
        'server' => 'सर्वर',
        'user' => 'उपयोगकर्ता',
        'subuser' => 'उप-उपयोगकर्ता',
        'node' => 'नोड',
        'location' => 'स्थान',
        'nest' => 'घोंसला',
        'egg' => 'एग',
        'mount' => 'माउंट',
        'database_host' => 'डेटाबेस होस्ट',
        'database' => 'डेटाबेस',
        'backup' => 'बैकअप',
        'schedule' => 'शेड्यूल',
        'role' => 'भूमिका',
        'alert' => 'अलर्ट',
    ],

    'model_actions' => [
        'created' => 'निर्माण',
        'updated' => 'अपडेट',
        'deleted' => 'विलोपन',
    ],

    'events' => [
        'model' => ':resource: :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'सिस्टम',
    ],

    'columns' => [
        'name' => 'नाम',
        'type' => 'प्रकार',
        'endpoint' => 'यहाँ भेजता है',
        'events' => 'इवेंट',
        'last_delivery' => 'पिछली डिलीवरी',
        'enabled' => 'सक्षम',
    ],

    'statuses' => [
        'delivered' => 'डिलीवर हुआ',
        'failed' => 'विफल',
        'never' => 'अभी तक कुछ नहीं भेजा गया',
    ],

    'deliveries' => [
        'title' => 'डिलीवरी',
        'label' => 'डिलीवरी',
        'plural_label' => 'डिलीवरी',
        'description' => 'पिछले :days दिनों में इस वेबहुक को भेजा गया सब कुछ।',
        'empty' => 'अभी तक कुछ नहीं भेजा गया',
        'empty_description' => 'चुने गए इवेंट में से कोई होते ही डिलीवरी यहाँ दिखती हैं। अभी आज़माने के लिए टेस्ट भेजें का उपयोग करें।',
        'no_response' => 'कोई उत्तर नहीं',
        'no_body' => 'एंडपॉइंट ने कुछ वापस नहीं भेजा।',
        'disabled' => 'इसे दोबारा भेजने के लिए वेबहुक सक्षम करें।',
        'columns' => [
            'id' => 'डिलीवरी ID',
            'status' => 'डिलीवर हुआ',
            'event' => 'इवेंट',
            'response' => 'उत्तर',
            'attempts' => 'प्रयास',
            'duration' => 'समय लगा',
            'sent' => 'भेजा गया',
            'payload' => 'पेलोड',
            'body' => 'उत्तर बॉडी',
        ],
    ],

    'actions' => [
        'test' => 'टेस्ट भेजें',
        'duplicate' => 'डुप्लिकेट',
        'redeliver' => 'दोबारा भेजें',
        'add_header' => 'हेडर जोड़ें',
        'generate_secret' => 'जनरेट करें',
        'select_all' => 'सभी चुनें',
        'deselect_all' => 'सभी का चयन हटाएँ',
    ],

    'notices' => [
        'delivered' => 'टेस्ट डिलीवर हो गया।',
        'failed' => 'टेस्ट डिलीवर नहीं हो सका।',
        'responded' => 'एंडपॉइंट ने :ms ms में :status उत्तर दिया।',
        'duplicated' => 'वेबहुक डुप्लिकेट किया गया और अक्षम छोड़ा गया।',
        'redelivery_queued' => 'इवेंट फिर से भेजा जा रहा है।',
    ],

    'validation' => [
        'events' => 'कम से कम एक इवेंट चुनें।',
        'header_name' => '":name" मान्य हेडर नाम नहीं है।',
        'header_reserved' => '":name" हेडर पैनल द्वारा सेट किया जाता है और बदला नहीं जा सकता।',
    ],

    'errors' => [
        'invalid_endpoint' => 'एंडपॉइंट मान्य http या https पता नहीं है।',
        'unresolvable' => ':host का पता नहीं मिल सका।',
        'private_endpoint' => ':host किसी निजी या आरक्षित पते की ओर इशारा करता है। अपने नेटवर्क के अंदर डिलीवरी की अनुमति देने के लिए APP_WEBHOOK_ALLOW_PRIVATE=true सेट करें।',
        'unreachable' => ':host से कनेक्ट नहीं हो सका।',
        'curl_required' => 'होस्टनेम पर डिलीवरी के लिए PHP cURL एक्सटेंशन ज़रूरी है, जो इंस्टॉल नहीं है।',
    ],

    'copy_suffix' => ':name (प्रति)',

];
