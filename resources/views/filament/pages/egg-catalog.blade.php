<x-filament-panels::page class="egg-catalog-page">
    <div wire:init="loadCatalog" class="flex flex-col gap-6">
        @if ($loaded && collect($statuses)->contains(fn ($status) => ($status['error'] && $status['error'] !== 'cooldown') || $status['stale'] || $status['invalid']))
        <div aria-live="polite" class="space-y-2">
            @foreach ($statuses as $provider => $status)
                @if ($loaded && (($status['error'] && $status['error'] !== 'cooldown') || $status['stale'] || $status['invalid']))
                    <p role="status" class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ $provider }}:</span>
                        @if ($status['error'] && $status['error'] !== 'cooldown') {{ trans('admin/catalog.errors.'.$status['error']) }}
                        @elseif ($status['stale']) {{ trans('admin/catalog.stale') }} @endif
                        @if ($status['invalid']) {{ trans('admin/catalog.partial', ['count' => $status['invalid']]) }} @endif
                    </p>
                @endif
            @endforeach
        </div>
        @endif
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="catalog-search" class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">{{ trans('admin/catalog.search') }}</label>
                <x-filament::input.wrapper><x-filament::input id="catalog-search" type="search" wire:model.live.debounce.300ms="search" /></x-filament::input.wrapper>
            </div>
            <div>
                <label for="catalog-source" class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">{{ trans('admin/catalog.source') }}</label>
                <x-filament::input.wrapper><x-filament::input.select id="catalog-source" wire:model.live="source">
                    <option value="">{{ trans('admin/catalog.all_sources') }}</option>
                    @foreach (\App\Services\Eggs\Catalog\EggCatalogService::PROVIDERS as $provider)<option value="{{ $provider }}">{{ $provider }}</option>@endforeach
                </x-filament::input.select></x-filament::input.wrapper>
            </div>
            <div>
                <label for="catalog-category" class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">{{ trans('admin/catalog.category') }}</label>
                <x-filament::input.wrapper><x-filament::input.select id="catalog-category" wire:model.live="category">
                    <option value="">{{ trans('admin/catalog.all_categories') }}</option>
                    @foreach ($categories as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </x-filament::input.select></x-filament::input.wrapper>
            </div>
        </div>
        <div wire:loading wire:target="loadCatalog" role="status" class="text-sm text-gray-600 dark:text-gray-400">{{ trans('admin/catalog.loading') }}</div>
        @if ($loaded)
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="opacity-60" wire:target="search,source,category,refreshCatalog">
                @forelse ($visible as $entry)
                    <article wire:key="egg-{{ $entry->key() }}" class="flex h-72 min-w-0 flex-col gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="flex h-12 shrink-0 items-start gap-3">
                            <x-filament::icon icon="tabler-egg" class="mt-1 h-6 w-6 shrink-0 text-gray-400" />
                            <h2 title="{{ $entry->name }}" class="line-clamp-2 min-w-0 break-words text-base font-semibold text-gray-950 dark:text-white">{{ $entry->name }}</h2>
                        </div>
                        <div class="flex h-6 shrink-0 items-center gap-2"><x-filament::badge color="gray">{{ $entry->provider }}</x-filament::badge><span class="truncate text-sm text-gray-600 dark:text-gray-400">{{ $this->categoryLabel($entry) }}</span></div>
                        <p class="line-clamp-3 h-15 shrink-0 break-words text-sm text-gray-600 dark:text-gray-400">{{ $entry->description ?: trans('admin/catalog.no_description') }}</p>
                        <div class="h-5 shrink-0">
                            @if (isset($imports[$entry->key()]))<p class="truncate text-sm text-gray-600 dark:text-gray-400" title="{{ trans('admin/catalog.already_imported') }}">{{ trans('admin/catalog.already_imported') }}</p>@endif
                        </div>
                        <div class="mt-auto pt-2" x-data="{ reviewing: false }">
                            <x-filament::button color="gray" size="sm" x-on:click="reviewing = true; try { await $wire.mountAction('review', {{ \Illuminate\Support\Js::from(['provider' => $entry->provider, 'id' => $entry->id]) }}) } finally { reviewing = false }" x-bind:disabled="reviewing" x-bind:aria-busy="reviewing" aria-label="{{ trans('admin/catalog.review_named', ['egg' => $entry->name, 'provider' => $entry->provider]) }}">{{ trans('admin/catalog.review') }}<x-filament::loading-indicator x-cloak x-show="reviewing" class="h-4 w-4" /></x-filament::button>
                        </div>
                    </article>
                @empty
                    <p class="sm:col-span-2 xl:col-span-3 text-sm text-gray-600 dark:text-gray-400">{{ trans('admin/catalog.'.$emptyReason) }}</p>
                @endforelse
            </div>
            @if ($total > 0)
                <div class="fi-ta-ctn fi-ta-ctn-with-footer">
                    <div class="fi-ta-main">
                        <x-filament::pagination :paginator="$paginator" :page-options="[24, 48, 96]" current-page-option-property="perPage" />
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
