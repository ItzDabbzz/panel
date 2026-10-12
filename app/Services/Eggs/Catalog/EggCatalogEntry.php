<?php

namespace App\Services\Eggs\Catalog;

final readonly class EggCatalogEntry
{
    public function __construct(
        public string $provider,
        public string $id,
        public string $name,
        public string $description,
        public string $category,
        public string $repository,
        public string $path,
        public string $sourceUrl,
        private string $downloadUrl,
        public ?string $revision = null,
        public ?string $author = null,
        public array $metadata = [],
    ) {}

    public function key(): string
    {
        return hash('sha256', $this->provider."\0".$this->id);
    }

    public function downloadUrl(): string
    {
        return CatalogUrl::validate($this->downloadUrl, $this->repository, $this->path);
    }

    public function githubUrl(): string
    {
        return 'https://github.com/'.$this->repository.'/blob/'.rawurlencode($this->revision ?? 'main').'/'.CatalogUrl::encodePath($this->path);
    }

    public function origin(string $bytes): array
    {
        return [
            'provider' => $this->provider, 'entry_id' => $this->id,
            'repository' => $this->repository, 'file_path' => $this->path,
            'source_url' => $this->sourceUrl, 'download_url' => $this->downloadUrl(),
            'revision' => $this->revision, 'imported_at' => now()->toIso8601String(),
            'content_sha256' => hash('sha256', $bytes),
        ];
    }
}
