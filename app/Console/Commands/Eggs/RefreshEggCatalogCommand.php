<?php

namespace App\Console\Commands\Eggs;

use App\Services\Eggs\Catalog\EggCatalogService;
use Illuminate\Console\Command;

class RefreshEggCatalogCommand extends Command
{
    protected $signature = 'p:eggs:refresh-catalog';

    protected $description = 'Refresh the stored egg catalogs.';

    public function handle(EggCatalogService $catalog): int
    {
        $failed = false;
        foreach (EggCatalogService::PROVIDERS as $provider) {
            $status = $catalog->provider($provider, true);
            if (in_array($status['error'], ['cooldown', 'refreshing'], true)) {
                $this->info($provider.': skipped ('.$status['error'].')');
            } elseif ($status['error']) {
                $this->warn($provider.': '.$status['error']);
                $failed = true;
            } else {
                $this->info($provider.': '.count($status['entries']).' entries');
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
