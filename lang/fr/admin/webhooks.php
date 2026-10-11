<?php

return [

    'label' => 'Webhook',
    'plural_label' => 'Webhooks',

    'sections' => [
        'details' => 'Détails',
        'details_description' => 'Les webhooks préviennent un autre service dès que quelque chose se produit sur le panel.',
        'delivery' => 'Livraison',
        'delivery_description' => 'Où les événements sont envoyés et comment le destinataire peut leur faire confiance.',
        'message' => 'Message Discord',
        'message_description' => 'Ce qui est publié dans le salon pour chaque événement.',
        'events' => 'Événements',
        'events_description' => 'Choisissez ce dont ce webhook est informé. Au moins un événement est requis.',
    ],

    'fields' => [
        'name' => 'Nom',
        'description' => 'Description',
        'enabled' => 'Activé',
        'endpoint' => 'URL du point de terminaison',
        'type' => 'Type',
        'secret' => 'Secret de signature',
        'headers' => 'En-têtes personnalisés',
        'header_name' => 'En-tête',
        'header_value' => 'Valeur',
        'message_username' => 'Nom affiché',
        'message_avatar' => 'URL de l\'avatar',
        'message_content' => 'Message',
        'message_title' => 'Titre de l\'embed',
        'message_description' => 'Description de l\'embed',
        'message_color' => 'Couleur de l\'embed',
        'message_footer' => 'Pied de page de l\'embed',
        'message_timestamp' => 'Afficher le moment de l\'événement',
    ],

    'placeholders' => [
        'name' => 'Synchronisation de facturation',
        'description' => 'À quoi sert ce webhook.',
    ],

    'helpers' => [
        'secret' => 'Si défini, chaque requête comporte un en-tête X-Webhook-Signature contenant "sha256=" suivi du HMAC du corps de la requête calculé avec ce secret.',
        'message_variables' => 'Utilisez {{label}} pour le nom de l\'événement, {{summary}} pour une phrase qui le décrit, {{event}}, {{timestamp}}, {{panel}} et {{panel_url}}. Tout ce que contient l\'événement peut aussi être utilisé, par exemple {{data.name}} ou {{data.actor.name}}.',
    ],

    'types' => [
        'standard' => 'Standard',
        'discord' => 'Discord',
    ],

    'groups' => [
        'resources' => 'Enregistrements',
        'auth' => 'Activité de connexion',
        'account' => 'Activité du compte',
        'server_power' => 'Alimentation et console du serveur',
        'server_files' => 'Fichiers du serveur',
        'server_backups' => 'Sauvegardes du serveur',
        'server_databases' => 'Bases de données du serveur',
        'server_schedules' => 'Planifications du serveur',
        'server_settings' => 'Paramètres du serveur',
        'admin' => 'Administration',
    ],

    'resources' => [
        'server' => 'Serveur',
        'user' => 'Utilisateur',
        'subuser' => 'Sous-utilisateur',
        'node' => 'Noeud',
        'location' => 'Emplacement',
        'nest' => 'Nid',
        'egg' => 'Egg',
        'mount' => 'Support',
        'database_host' => 'Hôte de base de données',
        'database' => 'Base de données',
        'backup' => 'Sauvegarde',
        'schedule' => 'Planification',
        'role' => 'Rôle',
        'alert' => 'Alerte',
    ],

    'model_actions' => [
        'created' => 'création',
        'updated' => 'mise à jour',
        'deleted' => 'suppression',
    ],

    'events' => [
        'model' => ':resource : :action',
    ],

    'summary' => [
        'model' => ':label: :name',
        'activity' => ':actor: :label',
        'activity_on' => ':actor: :label (:subjects)',
        'system' => 'Système',
    ],

    'columns' => [
        'name' => 'Nom',
        'type' => 'Type',
        'endpoint' => 'Envoie à',
        'events' => 'Événements',
        'last_delivery' => 'Dernière livraison',
        'enabled' => 'Activé',
    ],

    'statuses' => [
        'delivered' => 'Livré',
        'failed' => 'Échec',
        'never' => 'Rien n\'a encore été envoyé',
    ],

    'deliveries' => [
        'title' => 'Livraisons',
        'label' => 'livraison',
        'plural_label' => 'livraisons',
        'description' => 'Tout ce qui a été envoyé à ce webhook au cours des :days derniers jours.',
        'empty' => 'Rien n\'a encore été envoyé',
        'empty_description' => 'Les livraisons apparaissent ici dès que l\'un des événements choisis se produit. Utilisez Envoyer un test pour essayer maintenant.',
        'no_response' => 'Aucune réponse',
        'no_body' => 'Le point de terminaison n\'a rien renvoyé.',
        'disabled' => 'Activez le webhook pour renvoyer ceci.',
        'columns' => [
            'id' => 'ID de livraison',
            'status' => 'Livré',
            'event' => 'Événement',
            'response' => 'Réponse',
            'attempts' => 'Tentatives',
            'duration' => 'Durée',
            'sent' => 'Envoyé',
            'payload' => 'Contenu',
            'body' => 'Corps de la réponse',
        ],
    ],

    'actions' => [
        'test' => 'Envoyer un test',
        'duplicate' => 'Dupliquer',
        'redeliver' => 'Renvoyer',
        'add_header' => 'Ajouter un en-tête',
        'generate_secret' => 'Générer',
        'select_all' => 'Tout sélectionner',
        'deselect_all' => 'Tout désélectionner',
    ],

    'notices' => [
        'delivered' => 'Le test a été livré.',
        'failed' => 'Le test n\'a pas pu être livré.',
        'responded' => 'Le point de terminaison a répondu :status en :ms ms.',
        'duplicated' => 'Le webhook a été dupliqué et laissé désactivé.',
        'redelivery_queued' => 'L\'événement est de nouveau en route.',
    ],

    'validation' => [
        'events' => 'Choisissez au moins un événement.',
        'header_name' => '":name" n\'est pas un nom d\'en-tête valide.',
        'header_reserved' => 'L\'en-tête ":name" est défini par le panel et ne peut pas être modifié.',
    ],

    'errors' => [
        'invalid_endpoint' => 'Le point de terminaison n\'est pas une adresse http ou https valide.',
        'unresolvable' => 'L\'adresse de :host est introuvable.',
        'private_endpoint' => ':host pointe vers une adresse privée ou réservée. Définissez APP_WEBHOOK_ALLOW_PRIVATE=true pour autoriser les livraisons au sein de votre propre réseau.',
        'unreachable' => 'Connexion à :host impossible.',
    ],

    'copy_suffix' => ':name (copie)',

];
