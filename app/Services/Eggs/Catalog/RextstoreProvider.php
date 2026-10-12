<?php

namespace App\Services\Eggs\Catalog;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RextstoreProvider
{
    public const TREE_URL = 'https://api.github.com/repos/reviactyl/eggs/git/trees/main?recursive=1';

    public function __construct(private CatalogHttpClient $http) {}

    public function definition(EggCatalogEntry $entry): string
    {
        return Cache::remember($this->definitionKey($entry->revision, $entry->path), 86400,
            fn () => $this->http->get($entry->downloadUrl(), CatalogHttpClient::EGG_LIMIT, definition: true));
    }

    private function definitionKey(string $revision, string $path): string
    {
        return 'egg-catalog:definition:'.hash('sha256', $revision."\0".$path);
    }

    public function fetch(): array
    {
        $tree = $this->http->json($this->http->get(self::TREE_URL, 10 * 1048576));
        if (($tree['truncated'] ?? null) !== false || ! is_string($tree['sha'] ?? null)
            || ! preg_match('/^[a-f0-9]{40}$/D', $tree['sha']) || ! is_array($tree['tree'] ?? null) || ! array_is_list($tree['tree'])) {
            throw new CatalogException('invalid_catalog');
        }
        $paths = [];
        $invalid = 0;
        foreach ($tree['tree'] as $blob) {
            if (! is_array($blob) || ($blob['type'] ?? '') !== 'blob' || ! is_string($blob['path'] ?? null)
                || ! preg_match('/(^|\/)egg-[^\/]+\.json$/D', $blob['path'])) {
                continue;
            }
            if (! CatalogUrl::path($blob['path'])) {
                $invalid++;

                continue;
            }
            $paths[] = $blob['path'];
        }
        $entries = [];
        foreach (array_chunk(array_unique($paths), 16) as $chunk) {
            $missing = array_filter($chunk, fn ($path) => ! Cache::has($this->definitionKey($tree['sha'], $path)));
            $responses = Http::pool(function (Pool $pool) use ($missing, $tree): void {
                foreach ($missing as $path) {
                    $this->http->request($pool->as($path), CatalogHttpClient::EGG_LIMIT)
                        ->get('https://raw.githubusercontent.com/reviactyl/eggs/'.$tree['sha'].'/'.CatalogUrl::encodePath($path));
                }
            }, concurrency: 4);
            foreach ($chunk as $path) {
                try {
                    $key = $this->definitionKey($tree['sha'], $path);
                    $bytes = Cache::get($key);
                    if (! is_string($bytes)) {
                        $bytes = $this->http->body($responses[$path], CatalogHttpClient::EGG_LIMIT, definition: true);
                        Cache::put($key, $bytes, 86400);
                    }
                    $data = $this->http->json($bytes);
                    if (! is_string($data['name'] ?? null) || trim($data['name']) === '' || ! is_array($data['meta'] ?? null)
                        || ! is_string($data['meta']['version'] ?? null)) {
                        throw new CatalogException('invalid_catalog');
                    }
                    $category = str_contains($path, '/') ? explode('/', $path)[0] : '';
                    $github = 'https://github.com/reviactyl/eggs/blob/'.$tree['sha'].'/'.CatalogUrl::encodePath($path);
                    $source = substr_count($path, '/') === 1
                        ? 'https://rextstore.app/eggs/?'.http_build_query(['nest' => $category, 'egg' => substr(basename($path), 4, -5)], '', '&', PHP_QUERY_RFC3986)
                        : $github;
                    $entries[] = new EggCatalogEntry('Rextstore', $path, $data['name'],
                        is_string($data['description'] ?? null) ? $data['description'] : '', $category,
                        'reviactyl/eggs', $path, $source,
                        'https://raw.githubusercontent.com/reviactyl/eggs/'.$tree['sha'].'/'.CatalogUrl::encodePath($path),
                        $tree['sha'], is_string($data['author'] ?? null) ? $data['author'] : null);
                } catch (CatalogException $exception) {
                    if ($exception->reason === 'rate_limited') {
                        throw $exception;
                    }
                    $invalid++;
                }
            }
        }
        if (($paths !== [] || $invalid > 0) && $entries === []) {
            throw new CatalogException('invalid_catalog');
        }

        return ['entries' => $entries, 'invalid' => $invalid];
    }
}
