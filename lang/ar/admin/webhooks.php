<?php

return [

    'label' => 'ويب هوك',
    'plural_label' => 'ويب هوك',

    'sections' => [
        'details' => 'التفاصيل',
        'details_description' => 'يُبلّغ الويب هوك خدمة أخرى لحظة حدوث شيء في اللوحة.',
        'delivery' => 'التسليم',
        'delivery_description' => 'إلى أين تُرسل الأحداث وكيف يمكن للطرف المستقبِل الوثوق بها.',
        'message' => 'رسالة Discord',
        'message_description' => 'ما يُنشر في القناة لكل حدث.',
        'events' => 'الأحداث',
        'events_description' => 'اختر ما يُبلَّغ به هذا الويب هوك. مطلوب حدث واحد على الأقل.',
    ],

    'fields' => [
        'name' => 'الاسم',
        'description' => 'الوصف',
        'enabled' => 'مفعّل',
        'endpoint' => 'رابط نقطة النهاية',
        'type' => 'النوع',
        'secret' => 'مفتاح التوقيع',
        'headers' => 'ترويسات مخصصة',
        'header_name' => 'الترويسة',
        'header_value' => 'القيمة',
        'message_username' => 'الاسم المعروض',
        'message_avatar' => 'رابط الصورة الرمزية',
        'message_content' => 'الرسالة',
        'message_title' => 'عنوان التضمين',
        'message_description' => 'وصف التضمين',
        'message_color' => 'لون التضمين',
        'message_footer' => 'تذييل التضمين',
        'message_timestamp' => 'إظهار وقت حدوث الحدث',
    ],

    'placeholders' => [
        'name' => 'مزامنة الفوترة',
        'description' => 'الغرض من هذا الويب هوك.',
    ],

    'helpers' => [
        'secret' => 'عند تعيينه، يحمل كل طلب ترويسة X-Webhook-Signature تحتوي على "sha256=" متبوعة بقيمة HMAC لمحتوى الطلب المحسوبة بهذا المفتاح.',
        'message_variables' => 'استخدم {{label}} لاسم الحدث، و{{summary}} لجملة تصفه، و{{event}} و{{timestamp}} و{{panel}} و{{panel_url}}. يمكن أيضًا استخدام أي شيء في الحدث، مثل {{data.name}} أو {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'قياسي',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'السجلات',
        'auth' => 'نشاط تسجيل الدخول',
        'account' => 'نشاط الحساب',
        'server_power' => 'طاقة الخادم ووحدة التحكم',
        'server_files' => 'ملفات الخادم',
        'server_backups' => 'نسخ الخادم الاحتياطية',
        'server_databases' => 'قواعد بيانات الخادم',
        'server_schedules' => 'جداول الخادم',
        'server_settings' => 'إعدادات الخادم',
        'admin' => 'الإدارة',
    ],

    'resources' => [
        'server' => 'الخادم',
        'user' => 'المستخدم',
        'subuser' => 'المستخدم الفرعي',
        'node' => 'العقدة',
        'location' => 'الموقع',
        'nest' => 'العش',
        'egg' => 'البيضة',
        'mount' => 'التوصيلة',
        'database_host' => 'خادم قواعد البيانات',
        'database' => 'قاعدة البيانات',
        'backup' => 'النسخة الاحتياطية',
        'schedule' => 'الجدول',
        'role' => 'الدور',
        'alert' => 'التنبيه',
    ],

    'model_actions' => [
        'created' => 'إنشاء',
        'updated' => 'تحديث',
        'deleted' => 'حذف',
    ],

    'events' => [
        'model' => ':resource: :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'النظام',
    ],

    'columns' => [
        'name' => 'الاسم',
        'type' => 'النوع',
        'endpoint' => 'يُرسل إلى',
        'events' => 'الأحداث',
        'last_delivery' => 'آخر تسليم',
        'enabled' => 'مفعّل',
    ],

    'statuses' => [
        'delivered' => 'تم التسليم',
        'failed' => 'فشل',
        'never' => 'لم يُرسل شيء بعد',
    ],

    'deliveries' => [
        'title' => 'عمليات التسليم',
        'label' => 'عملية تسليم',
        'plural_label' => 'عمليات تسليم',
        'description' => 'كل ما أُرسل إلى هذا الويب هوك خلال آخر :days يومًا.',
        'empty' => 'لم يُرسل شيء بعد',
        'empty_description' => 'تظهر عمليات التسليم هنا فور حدوث أحد الأحداث المختارة. استخدم «إرسال اختبار» لتجربته الآن.',
        'no_response' => 'لا يوجد رد',
        'no_body' => 'لم تُرجع نقطة النهاية أي شيء.',
        'disabled' => 'فعّل الويب هوك لإرسال هذا مرة أخرى.',
        'columns' => [
            'id' => 'معرّف التسليم',
            'status' => 'تم التسليم',
            'event' => 'الحدث',
            'response' => 'الرد',
            'attempts' => 'المحاولات',
            'duration' => 'المدة',
            'sent' => 'أُرسل',
            'payload' => 'المحتوى',
            'body' => 'محتوى الرد',
        ],
    ],

    'actions' => [
        'test' => 'إرسال اختبار',
        'duplicate' => 'تكرار',
        'redeliver' => 'إرسال مرة أخرى',
        'add_header' => 'إضافة ترويسة',
        'generate_secret' => 'توليد',
        'select_all' => 'تحديد الكل',
        'deselect_all' => 'إلغاء تحديد الكل',
    ],

    'notices' => [
        'delivered' => 'تم تسليم الاختبار.',
        'failed' => 'تعذّر تسليم الاختبار.',
        'responded' => 'ردّت نقطة النهاية بـ :status خلال :ms مللي ثانية.',
        'duplicated' => 'تم تكرار الويب هوك وتُرك معطّلًا.',
        'redelivery_queued' => 'الحدث في طريقه مرة أخرى.',
    ],

    'validation' => [
        'events' => 'اختر حدثًا واحدًا على الأقل.',
        'header_name' => '":name" ليس اسم ترويسة صالحًا.',
        'header_reserved' => 'الترويسة ":name" تضبطها اللوحة ولا يمكن تغييرها.',
    ],

    'errors' => [
        'invalid_endpoint' => 'نقطة النهاية ليست عنوان http أو https صالحًا.',
        'unresolvable' => 'تعذّر العثور على عنوان :host.',
        'private_endpoint' => 'يشير :host إلى عنوان خاص أو محجوز. عيّن APP_WEBHOOK_ALLOW_PRIVATE=true للسماح بالتسليم داخل شبكتك.',
        'unreachable' => 'تعذّر الاتصال بـ :host.',
    ],

    'copy_suffix' => ':name (نسخة)',

];
