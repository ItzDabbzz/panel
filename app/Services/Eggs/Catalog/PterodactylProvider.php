<?php

namespace App\Services\Eggs\Catalog;

class PterodactylProvider
{
    public const INDEX_URL = 'https://eggs.pterodactyl.io/api/eggs.json';

    public function __construct(private CatalogHttpClient $http) {}

    public function fetch(): array
    {
        $records = $this->http->json($this->http->get(self::INDEX_URL, 5 * 1048576));
        if (! array_is_list($records)) {
            throw new CatalogException('invalid_catalog');
        }
        $entries = [];
        $invalid = 0;
        foreach ($records as $record) {
            try {
                if (! is_array($record)) {
                    throw new CatalogException('invalid_catalog');
                }
                foreach (['id', 'name', 'repo', 'filePath', 'downloadUrl'] as $field) {
                    if (! is_string($record[$field] ?? null) || trim($record[$field]) === '') {
                        throw new CatalogException('invalid_catalog');
                    }
                }
                $repo = str_replace('https://github.com/', '', $record['repo']);
                $category = match ($repo) {
                    'pterodactyl/game-eggs' => 'Games',
                    'pterodactyl/application-eggs' => 'Applications',
                    'pterodactyl/generic-eggs' => 'Languages',
                    default => throw new CatalogException('url'),
                };
                $url = CatalogUrl::validate($record['downloadUrl'], $repo, $record['filePath']);
                if (isset($entries[$record['id']])) {
                    throw new CatalogException('invalid_catalog');
                }
                $entries[$record['id']] = new EggCatalogEntry('Pterodactyl', $record['id'], $record['name'],
                    is_string($record['description'] ?? null) ? $record['description'] : '', $category,
                    $repo, $record['filePath'], 'https://eggs.pterodactyl.io/egg/'.rawurlencode($record['id']), $url,
                    explode('/', parse_url($url, PHP_URL_PATH))[3], metadata: array_intersect_key($record, array_flip(['lastUpdated', 'hash'])));
            } catch (CatalogException $exception) {
                $invalid++;
            }
        }
        if ($records !== [] && $entries === []) {
            throw new CatalogException('invalid_catalog');
        }

        return ['entries' => array_values($entries), 'invalid' => $invalid];
    }
}
