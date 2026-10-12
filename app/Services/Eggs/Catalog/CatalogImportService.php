<?php

namespace App\Services\Eggs\Catalog;

use App\Models\Egg;
use App\Services\Eggs\Sharing\EggImporterService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogImportService
{
    public function __construct(private EggCatalogService $catalog, private CatalogDefinition $definition, private CatalogAccess $access, private EggImporterService $importer) {}

    public function stage(string $provider, string $id): string
    {
        $user = $this->access->authorize();
        $this->catalog->provider($provider);
        $entry = $this->catalog->resolve($provider, $id);
        $bytes = $this->catalog->download($entry);
        $parsed = $this->definition->parse($bytes);
        $token = Str::random(64);
        Cache::put($this->stageKey($token), [
            'user_id' => $user->id, 'entry' => $entry, 'bytes' => $bytes, 'parsed' => $parsed,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], 600);

        return $token;
    }

    private function stageKey(string $token): string
    {
        return 'egg-catalog:stage:'.hash('sha256', $token);
    }

    public function review(string $token): array
    {
        $user = $this->access->authorize();
        $stage = Cache::get($this->stageKey($token));
        if (! is_array($stage) || $stage['user_id'] !== $user->id || $stage['expires_at'] <= now()->timestamp) {
            throw new CatalogException('expired');
        }

        return $stage;
    }

    public function existing(string $token, int $nestId): ?Egg
    {
        $stage = $this->review($token);
        $this->access->nest($nestId);

        return Egg::query()->where('nest_id', $nestId)->where('catalog_key', $stage['entry']->key())->first();
    }

    public function import(string $token, int $nestId): Egg
    {
        $this->access->authorize(true);
        $stage = $this->review($token);
        $nest = $this->access->nest($nestId);
        $entry = $stage['entry'];
        if ($existing = Egg::query()->where('nest_id', $nestId)->where('catalog_key', $entry->key())->first()) {
            return $existing;
        }
        try {
            return $this->definition->withFile($stage['bytes'], function (UploadedFile $file) use ($nest, $entry, $stage): Egg {
                return DB::transaction(function () use ($file, $nest, $entry, $stage): Egg {
                    $this->access->nest($nest->id);
                    $egg = $this->importer->handle($file, $nest->id);
                    $egg->forceFill(['catalog_key' => $entry->key(), 'catalog_origin' => $entry->origin($stage['bytes'])])->save();

                    return $egg;
                });
            });
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'eggs_nest_catalog_unique')
                && ! str_contains($exception->getMessage(), 'eggs.nest_id, eggs.catalog_key')) {
                throw $exception;
            }

            return Egg::query()->where('nest_id', $nestId)->where('catalog_key', $entry->key())->first() ?? throw $exception;
        }
    }
}
