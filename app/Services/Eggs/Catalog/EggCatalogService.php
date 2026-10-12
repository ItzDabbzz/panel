<?php

namespace App\Services\Eggs\Catalog;

use Illuminate\Support\Facades\Cache;

class EggCatalogService
{
    public const PROVIDERS = ['Rextstore', 'Pterodactyl'];

    public function __construct(private RextstoreProvider $rextstore, private PterodactylProvider $pterodactyl, private CatalogHttpClient $http) {}

    public function provider(string $provider, bool $refresh = false, bool $manualRefresh = false): array
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            throw new CatalogException('unknown');
        }
        $key = 'egg-catalog:provider:'.$provider;
        $cached = Cache::get($key);
        $error = Cache::get($key.':error');
        if (! $refresh && is_array($cached) && $cached['refreshed_at'] > now()->timestamp - 1800) {
            return $this->status($cached, $error);
        }
        $lock = Cache::lock($key.':lock', 300);
        if (! $lock->get()) {
            return $this->status($cached, $error ?? 'refreshing');
        }
        try {
            if (Cache::has($key.':cooldown') && (! $manualRefresh || $error === 'rate_limited')) {
                return $this->status($cached, $error ?? 'cooldown');
            }
            Cache::put($key.':cooldown', true, 60);
            try {
                $result = match ($provider) {
                    'Rextstore' => $this->rextstore->fetch(),
                    'Pterodactyl' => $this->pterodactyl->fetch(),
                };
                $cached = $result + ['refreshed_at' => now()->timestamp];
                Cache::put($key, $cached, 86400);
                Cache::forget($key.':error');
                $error = null;
            } catch (\Throwable $exception) {
                $error = $exception instanceof CatalogException ? $exception->reason : 'unavailable';
                Cache::put($key.':error', $error, 86400);
                if ($exception instanceof CatalogException && $exception->reason === 'rate_limited') {
                    Cache::put($key.':cooldown', true, $exception->retryAfter);
                }
            }

            return $this->status($cached, $error);
        } finally {
            $lock->release();
        }
    }

    private function status(?array $cached, ?string $error): array
    {
        return [
            'entries' => $cached['entries'] ?? [], 'invalid' => $cached['invalid'] ?? 0,
            'refreshed_at' => $cached['refreshed_at'] ?? null, 'error' => $error,
            'stale' => $cached !== null && ($error !== null || $cached['refreshed_at'] <= now()->timestamp - 1800),
        ];
    }

    public function cached(string $provider): array
    {
        return $this->status(Cache::get('egg-catalog:provider:'.$provider), Cache::get('egg-catalog:provider:'.$provider.':error'));
    }

    public function resolve(string $provider, string $id): EggCatalogEntry
    {
        foreach ($this->cached($provider)['entries'] as $entry) {
            if ($entry->id === $id) {
                return $entry;
            }
        }

        throw new CatalogException('unknown');
    }

    public function download(EggCatalogEntry $entry): string
    {
        return $entry->provider === 'Rextstore' ? $this->rextstore->definition($entry)
            : $this->http->get($entry->downloadUrl(), CatalogHttpClient::EGG_LIMIT, definition: true);
    }
}
