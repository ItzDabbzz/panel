<div class="flex flex-col gap-4 break-words text-sm text-gray-950 dark:text-white">
    @if ($error)<p role="alert">{{ trans('admin/catalog.errors.'.$error) }}</p>@endif
    @if ($entry)
        <h3 class="text-lg font-semibold">{{ $parsed['name'] ?? $entry->name }}</h3>
        <p class="whitespace-pre-line text-gray-600 dark:text-gray-400">{{ $parsed['description'] ?? $entry->description }}</p>
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div><dt class="text-gray-600 dark:text-gray-400">{{ trans('admin/catalog.source') }}</dt><dd>{{ $entry->provider }}</dd></div>
            <div><dt class="text-gray-600 dark:text-gray-400">{{ trans('admin/catalog.repository') }}</dt><dd>{{ $entry->repository }}</dd></div>
            @if ($parsed['author'] ?? $entry->author)<div><dt class="text-gray-600 dark:text-gray-400">{{ trans('admin/eggs.fields.author') }}</dt><dd>{{ $parsed['author'] ?? $entry->author }}</dd></div>@endif
            @if ($parsed)
                <div><dt class="text-gray-600 dark:text-gray-400">{{ trans('admin/catalog.format') }}</dt><dd>{{ $parsed['meta']['version'] }}</dd></div>
                <div><dt class="text-gray-600 dark:text-gray-400">{{ trans('admin/catalog.variables') }}</dt><dd>{{ count($parsed['variables']) }}</dd></div>
            @endif
        </dl>
        <div class="flex flex-wrap gap-4">
            <x-filament::link :href="$entry->sourceUrl" target="_blank" rel="noopener noreferrer">{{ trans('admin/catalog.source_link') }}</x-filament::link>
            <x-filament::link :href="$entry->githubUrl()" target="_blank" rel="noopener noreferrer">{{ trans('admin/catalog.github_link') }}</x-filament::link>
        </div>
        @if ($parsed)
            <div><h4 class="font-medium">{{ trans('admin/eggs.sections.docker_images.title') }}</h4><ul class="mt-2 space-y-1">@foreach ($parsed['docker_images'] as $label => $image)<li>{{ $label }}: <code class="break-all">{{ $image }}</code></li>@endforeach</ul></div>
            <details><summary class="cursor-pointer font-medium">{{ trans('admin/eggs.fields.startup') }}</summary><pre class="mt-2 max-h-64 overflow-auto whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-xs dark:bg-gray-950">{{ $parsed['startup'] ?? '' }}</pre></details>
            <details><summary class="cursor-pointer font-medium">{{ trans('admin/catalog.script') }}</summary><pre class="mt-2 max-h-64 overflow-auto whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-xs dark:bg-gray-950">{{ $parsed['scripts']['installation']['script'] ?? '' }}</pre></details>
        @endif
    @endif
    @if ($nests->isEmpty())
        <p role="status">{{ trans('admin/catalog.nest_required') }}</p>
        @if (\App\Filament\Resources\Nests\NestResource::canCreate())
            <x-filament::link :href="\App\Filament\Resources\Nests\NestResource::getUrl('create', ['catalog_return' => $resumeUrl])">{{ trans('admin/catalog.create_nest') }}</x-filament::link>
        @endif
    @endif
    @if ($existing)
        <p role="status">{{ trans('admin/catalog.existing_in', ['nest' => $nest->name]) }}</p>
    @endif
    @if ($sameName)<p role="status">{{ trans('admin/catalog.same_name') }}</p>@endif
    <p wire:loading wire:target="callMountedAction" role="status">{{ trans('admin/catalog.importing') }}</p>
</div>
