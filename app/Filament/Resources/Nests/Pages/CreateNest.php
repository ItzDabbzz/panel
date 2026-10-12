<?php

namespace App\Filament\Resources\Nests\Pages;

use App\Filament\Resources\Nests\EggResource;
use App\Filament\Resources\Nests\NestResource;
use App\Services\Nests\NestCreationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;

class CreateNest extends CreateRecord
{
    protected static string $resource = NestResource::class;

    #[Locked]
    public ?string $catalogReturn = null;

    public function mount(): void
    {
        parent::mount();
        $url = request()->query('catalog_return');
        $expected = EggResource::getUrl('catalog');
        if (is_string($url) && str_starts_with($url, $expected.'?') && parse_url($url, PHP_URL_PATH) === parse_url($expected, PHP_URL_PATH)
            && parse_url($url, PHP_URL_FRAGMENT) === null) {
            $this->catalogReturn = $url;
        }
    }

    protected function getRedirectUrl(): string
    {
        if ($this->catalogReturn !== null) {
            parse_str((string) parse_url($this->catalogReturn, PHP_URL_QUERY), $query);
            $query = array_filter(Arr::only($query, ['search', 'source', 'category', 'page', 'per_page', 'review_provider', 'review_id']), 'is_string');

            return EggResource::getUrl('catalog', $query + ['nest_id' => $this->record->getKey()]);
        }

        return parent::getRedirectUrl();
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(NestCreationService::class)->handle($data, $data['author'] ?? null);
    }
}
