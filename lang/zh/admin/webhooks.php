<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhook',

    'sections' => [
        'details' => '详情',
        'details_description' => 'Webhook 会在面板上发生事件的那一刻通知其他服务。',
        'delivery' => '投递',
        'delivery_description' => '事件发送到哪里，以及接收方如何信任它们。',
        'message' => 'Discord 消息',
        'message_description' => '每个事件在频道中发布的内容。',
        'events' => '事件',
        'events_description' => '选择此 Webhook 接收哪些通知。至少需要一个事件。',
    ],

    'fields' => [
        'name' => '名称',
        'description' => '描述',
        'enabled' => '已启用',
        'endpoint' => '端点 URL',
        'type' => '类型',
        'secret' => '签名密钥',
        'headers' => '自定义请求头',
        'header_name' => '请求头',
        'header_value' => '值',
        'message_username' => '显示名称',
        'message_avatar' => '头像 URL',
        'message_content' => '消息',
        'message_title' => '嵌入标题',
        'message_description' => '嵌入描述',
        'message_color' => '嵌入颜色',
        'message_footer' => '嵌入页脚',
        'message_timestamp' => '显示事件发生的时间',
    ],

    'placeholders' => [
        'name' => '账单同步',
        'description' => '此 Webhook 的用途。',
    ],

    'helpers' => [
        'secret' => '设置后，每个请求都会带有 X-Webhook-Signature 请求头，内容为 "sha256=" 加上用此密钥对请求正文计算出的 HMAC。',
        'message_variables' => '使用 {{label}} 表示事件名称，{{summary}} 表示描述事件的一句话，还可使用 {{event}}、{{timestamp}}、{{panel}} 和 {{panel_url}}。事件中的任何内容也都可以使用，例如 {{data.name}} 或 {{data.actor.name}}。',
    ],

    'types' => [
        'standard' => '标准',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => '记录',
        'auth' => '登录活动',
        'account' => '账户活动',
        'server_power' => '服务器电源与控制台',
        'server_files' => '服务器文件',
        'server_backups' => '服务器备份',
        'server_databases' => '服务器数据库',
        'server_schedules' => '服务器计划任务',
        'server_settings' => '服务器设置',
        'admin' => '管理',
    ],

    'resources' => [
        'server' => '服务器',
        'user' => '用户',
        'subuser' => '子用户',
        'node' => '节点',
        'location' => '区域',
        'nest' => '预设',
        'egg' => 'Egg',
        'mount' => '挂载',
        'database_host' => '数据库主机',
        'database' => '数据库',
        'backup' => '备份',
        'schedule' => '计划任务',
        'role' => '角色',
        'alert' => '公告',
    ],

    'model_actions' => [
        'created' => '已创建',
        'updated' => '已更新',
        'deleted' => '已删除',
    ],

    'events' => [
        'model' => ':resource:action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => '系统',
    ],

    'columns' => [
        'name' => '名称',
        'type' => '类型',
        'endpoint' => '发送至',
        'events' => '事件',
        'last_delivery' => '最近一次投递',
        'enabled' => '已启用',
    ],

    'statuses' => [
        'delivered' => '已送达',
        'failed' => '失败',
        'never' => '尚未发送任何内容',
    ],

    'deliveries' => [
        'title' => '投递记录',
        'label' => '投递',
        'plural_label' => '投递',
        'description' => '最近 :days 天内发送到此 Webhook 的所有内容。',
        'empty' => '尚未发送任何内容',
        'empty_description' => '所选事件之一发生后，投递记录会立即显示在这里。使用“发送测试”可立即试用。',
        'no_response' => '无响应',
        'no_body' => '端点没有返回任何内容。',
        'disabled' => '启用 Webhook 后才能再次发送。',
        'columns' => [
            'id' => '投递 ID',
            'status' => '已送达',
            'event' => '事件',
            'response' => '响应',
            'attempts' => '尝试次数',
            'duration' => '耗时',
            'sent' => '发送时间',
            'payload' => '内容',
            'body' => '响应正文',
        ],
    ],

    'actions' => [
        'test' => '发送测试',
        'duplicate' => '复制',
        'redeliver' => '再次发送',
        'add_header' => '添加请求头',
        'generate_secret' => '生成',
        'select_all' => '全选',
        'deselect_all' => '取消全选',
    ],

    'notices' => [
        'delivered' => '测试已送达。',
        'failed' => '测试无法送达。',
        'responded' => '端点在 :ms 毫秒内返回了 :status 。',
        'duplicated' => 'Webhook 已复制并保持禁用。',
        'redelivery_queued' => '事件已再次发出。',
    ],

    'validation' => [
        'events' => '请至少选择一个事件。',
        'header_name' => '":name" 不是有效的请求头名称。',
        'header_reserved' => '":name" 请求头由面板设置，无法更改。',
    ],

    'errors' => [
        'invalid_endpoint' => '端点不是有效的 http 或 https 地址。',
        'unresolvable' => '找不到 :host 的地址。',
        'private_endpoint' => ':host 指向私有或保留地址。设置 APP_WEBHOOK_ALLOW_PRIVATE=true 以允许向您自己的网络内投递。',
        'unreachable' => '无法连接到 :host 。',
        'curl_required' => '向主机名投递需要 PHP cURL 扩展，但该扩展尚未安装。',
    ],

    'copy_suffix' => ':name（副本）',

];
