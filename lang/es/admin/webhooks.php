<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhooks',

    'sections' => [
        'details' => 'Detalles',
        'details_description' => 'Los webhooks avisan a otro servicio en el momento en que ocurre algo en el panel.',
        'delivery' => 'Entrega',
        'delivery_description' => 'A dónde se envían los eventos y cómo puede confiar en ellos el receptor.',
        'message' => 'Mensaje de Discord',
        'message_description' => 'Lo que se publica en el canal por cada evento.',
        'events' => 'Eventos',
        'events_description' => 'Elige de qué se informa a este webhook. Se requiere al menos un evento.',
    ],

    'fields' => [
        'name' => 'Nombre',
        'description' => 'Descripción',
        'enabled' => 'Activado',
        'endpoint' => 'URL del endpoint',
        'type' => 'Tipo',
        'secret' => 'Secreto de firma',
        'headers' => 'Cabeceras personalizadas',
        'header_name' => 'Cabecera',
        'header_value' => 'Valor',
        'message_username' => 'Nombre visible',
        'message_avatar' => 'URL del avatar',
        'message_content' => 'Mensaje',
        'message_title' => 'Título del embed',
        'message_description' => 'Descripción del embed',
        'message_color' => 'Color del embed',
        'message_footer' => 'Pie del embed',
        'message_timestamp' => 'Mostrar cuándo ocurrió el evento',
    ],

    'placeholders' => [
        'name' => 'Sincronización de facturación',
        'description' => 'Para qué sirve este webhook.',
    ],

    'helpers' => [
        'secret' => 'Si se establece, cada solicitud lleva una cabecera X-Webhook-Signature con "sha256=" seguido del HMAC del cuerpo de la solicitud generado con este secreto.',
        'message_variables' => 'Usa {{label}} para el nombre del evento, {{summary}} para una frase que lo describe, {{event}}, {{timestamp}}, {{panel}} y {{panel_url}}. También puedes usar cualquier dato del evento, como {{data.name}} o {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Estándar',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Registros',
        'auth' => 'Actividad de inicio de sesión',
        'account' => 'Actividad de la cuenta',
        'server_power' => 'Energía y consola del servidor',
        'server_files' => 'Archivos del servidor',
        'server_backups' => 'Copias de seguridad del servidor',
        'server_databases' => 'Bases de datos del servidor',
        'server_schedules' => 'Programaciones del servidor',
        'server_settings' => 'Ajustes del servidor',
        'admin' => 'Administración',
    ],

    'resources' => [
        'server' => 'Servidor',
        'user' => 'Usuario',
        'subuser' => 'Subusuario',
        'node' => 'Nodo',
        'location' => 'Ubicación',
        'nest' => 'Nido',
        'egg' => 'Egg',
        'mount' => 'Montaje',
        'database_host' => 'Host de base de datos',
        'database' => 'Base de datos',
        'backup' => 'Copia de seguridad',
        'schedule' => 'Programación',
        'role' => 'Rol',
        'alert' => 'Alerta',
    ],

    'model_actions' => [
        'created' => 'creación',
        'updated' => 'actualización',
        'deleted' => 'eliminación',
    ],

    'events' => [
        'model' => ':resource: :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'Sistema',
    ],

    'columns' => [
        'name' => 'Nombre',
        'type' => 'Tipo',
        'endpoint' => 'Envía a',
        'events' => 'Eventos',
        'last_delivery' => 'Última entrega',
        'enabled' => 'Activado',
    ],

    'statuses' => [
        'delivered' => 'Entregado',
        'failed' => 'Fallido',
        'never' => 'Aún no se ha enviado nada',
    ],

    'deliveries' => [
        'title' => 'Entregas',
        'label' => 'entrega',
        'plural_label' => 'entregas',
        'description' => 'Todo lo enviado a este webhook en los últimos :days días.',
        'empty' => 'Aún no se ha enviado nada',
        'empty_description' => 'Las entregas aparecen aquí en cuanto ocurre uno de los eventos elegidos. Usa Enviar prueba para probarlo ahora.',
        'no_response' => 'Sin respuesta',
        'no_body' => 'El endpoint no devolvió nada.',
        'disabled' => 'Activa el webhook para volver a enviar esto.',
        'columns' => [
            'id' => 'ID de entrega',
            'status' => 'Entregado',
            'event' => 'Evento',
            'response' => 'Respuesta',
            'attempts' => 'Intentos',
            'duration' => 'Duración',
            'sent' => 'Enviado',
            'payload' => 'Contenido',
            'body' => 'Cuerpo de la respuesta',
        ],
    ],

    'actions' => [
        'test' => 'Enviar prueba',
        'duplicate' => 'Duplicar',
        'redeliver' => 'Enviar de nuevo',
        'add_header' => 'Añadir cabecera',
        'generate_secret' => 'Generar',
        'select_all' => 'Seleccionar todo',
        'deselect_all' => 'Deseleccionar todo',
    ],

    'notices' => [
        'delivered' => 'La prueba se entregó.',
        'failed' => 'No se pudo entregar la prueba.',
        'responded' => 'El endpoint respondió :status en :ms ms.',
        'duplicated' => 'El webhook se duplicó y quedó desactivado.',
        'redelivery_queued' => 'El evento está en camino de nuevo.',
    ],

    'validation' => [
        'events' => 'Elige al menos un evento.',
        'header_name' => '":name" no es un nombre de cabecera válido.',
        'header_reserved' => 'La cabecera ":name" la establece el panel y no se puede cambiar.',
    ],

    'errors' => [
        'invalid_endpoint' => 'El endpoint no es una dirección http o https válida.',
        'unresolvable' => 'No se pudo encontrar la dirección de :host.',
        'private_endpoint' => ':host apunta a una dirección privada o reservada. Establece APP_WEBHOOK_ALLOW_PRIVATE=true para permitir entregas dentro de tu propia red.',
        'unreachable' => 'No se pudo conectar con :host.',
    ],

    'copy_suffix' => ':name (copia)',

];
