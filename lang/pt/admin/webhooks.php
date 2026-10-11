<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhooks',

    'sections' => [
        'details' => 'Detalhes',
        'details_description' => 'Os webhooks avisam outro serviço no momento em que algo acontece no painel.',
        'delivery' => 'Entrega',
        'delivery_description' => 'Para onde os eventos são enviados e como o destinatário pode confiar neles.',
        'message' => 'Mensagem do Discord',
        'message_description' => 'O que é publicado no canal para cada evento.',
        'events' => 'Eventos',
        'events_description' => 'Escolha sobre o que este webhook é informado. Pelo menos um evento é obrigatório.',
    ],

    'fields' => [
        'name' => 'Nome',
        'description' => 'Descrição',
        'enabled' => 'Ativado',
        'endpoint' => 'URL do endpoint',
        'type' => 'Tipo',
        'secret' => 'Segredo de assinatura',
        'headers' => 'Cabeçalhos personalizados',
        'header_name' => 'Cabeçalho',
        'header_value' => 'Valor',
        'message_username' => 'Nome de exibição',
        'message_avatar' => 'URL do avatar',
        'message_content' => 'Mensagem',
        'message_title' => 'Título do embed',
        'message_description' => 'Descrição do embed',
        'message_color' => 'Cor do embed',
        'message_footer' => 'Rodapé do embed',
        'message_timestamp' => 'Mostrar quando o evento aconteceu',
    ],

    'placeholders' => [
        'name' => 'Sincronização de cobrança',
        'description' => 'Para que serve este webhook.',
    ],

    'helpers' => [
        'secret' => 'Quando definido, cada requisição leva um cabeçalho X-Webhook-Signature com "sha256=" seguido do HMAC do corpo da requisição gerado com este segredo.',
        'message_variables' => 'Use {{label}} para o nome do evento, {{summary}} para uma frase que o descreve, {{event}}, {{timestamp}}, {{panel}} e {{panel_url}}. Qualquer dado do evento também pode ser usado, como {{data.name}} ou {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Padrão',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Registros',
        'auth' => 'Atividade de login',
        'account' => 'Atividade da conta',
        'server_power' => 'Energia e console do servidor',
        'server_files' => 'Arquivos do servidor',
        'server_backups' => 'Backups do servidor',
        'server_databases' => 'Bancos de dados do servidor',
        'server_schedules' => 'Agendamentos do servidor',
        'server_settings' => 'Configurações do servidor',
        'admin' => 'Administração',
    ],

    'resources' => [
        'server' => 'Servidor',
        'user' => 'Usuário',
        'subuser' => 'Subusuário',
        'node' => 'Nó',
        'location' => 'Local',
        'nest' => 'Ninho',
        'egg' => 'Egg',
        'mount' => 'Montagem',
        'database_host' => 'Host de banco de dados',
        'database' => 'Banco de dados',
        'backup' => 'Backup',
        'schedule' => 'Agendamento',
        'role' => 'Cargo',
        'alert' => 'Alerta',
    ],

    'model_actions' => [
        'created' => 'criação',
        'updated' => 'atualização',
        'deleted' => 'exclusão',
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
        'name' => 'Nome',
        'type' => 'Tipo',
        'endpoint' => 'Envia para',
        'events' => 'Eventos',
        'last_delivery' => 'Última entrega',
        'enabled' => 'Ativado',
    ],

    'statuses' => [
        'delivered' => 'Entregue',
        'failed' => 'Falhou',
        'never' => 'Nada enviado ainda',
    ],

    'deliveries' => [
        'title' => 'Entregas',
        'label' => 'entrega',
        'plural_label' => 'entregas',
        'description' => 'Tudo o que foi enviado a este webhook nos últimos :days dias.',
        'empty' => 'Nada enviado ainda',
        'empty_description' => 'As entregas aparecem aqui assim que um dos eventos escolhidos acontece. Use Enviar teste para experimentar agora.',
        'no_response' => 'Sem resposta',
        'no_body' => 'O endpoint não retornou nada.',
        'disabled' => 'Ative o webhook para enviar isto novamente.',
        'columns' => [
            'id' => 'ID da entrega',
            'status' => 'Entregue',
            'event' => 'Evento',
            'response' => 'Resposta',
            'attempts' => 'Tentativas',
            'duration' => 'Duração',
            'sent' => 'Enviado',
            'payload' => 'Conteúdo',
            'body' => 'Corpo da resposta',
        ],
    ],

    'actions' => [
        'test' => 'Enviar teste',
        'duplicate' => 'Duplicar',
        'redeliver' => 'Enviar novamente',
        'add_header' => 'Adicionar cabeçalho',
        'generate_secret' => 'Gerar',
        'select_all' => 'Selecionar tudo',
        'deselect_all' => 'Desmarcar tudo',
    ],

    'notices' => [
        'delivered' => 'O teste foi entregue.',
        'failed' => 'Não foi possível entregar o teste.',
        'responded' => 'O endpoint respondeu :status em :ms ms.',
        'duplicated' => 'O webhook foi duplicado e deixado desativado.',
        'redelivery_queued' => 'O evento está a caminho novamente.',
    ],

    'validation' => [
        'events' => 'Escolha pelo menos um evento.',
        'header_name' => '":name" não é um nome de cabeçalho válido.',
        'header_reserved' => 'O cabeçalho ":name" é definido pelo painel e não pode ser alterado.',
    ],

    'errors' => [
        'invalid_endpoint' => 'O endpoint não é um endereço http ou https válido.',
        'unresolvable' => 'Não foi possível encontrar o endereço de :host.',
        'private_endpoint' => ':host aponta para um endereço privado ou reservado. Defina APP_WEBHOOK_ALLOW_PRIVATE=true para permitir entregas dentro da sua própria rede.',
        'unreachable' => 'Não foi possível conectar a :host.',
    ],

    'copy_suffix' => ':name (cópia)',

];
