<?php

use App\Filament\Resources\Extensions\Pages\ListExtensions;
use App\Filament\Resources\Nests\Pages\ListNests;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

require dirname(__DIR__, 2).'/vendor/autoload.php';
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $error): never {
    fwrite(STDERR, (string) $error);
    exit(1);
});

// Only the nest selector needs a database while building these action schemas.
// Keep fixture rendering isolated from any configured Panel database.
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
DB::purge('sqlite');
Illuminate\Support\Facades\Schema::create('nests', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
});

$livewire = new class extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public array $data = ['file' => []];
};

$page = ($argv[1] ?? '') === 'json' ? new ListNests() : new ListExtensions();
$actions = (new ReflectionMethod($page, 'getHeaderActions'))->invoke($page);
$action = collect($actions)->first(fn ($action) => in_array($action->getName(), ['import', 'uploadInstall']));
$schema = $action->getSchema(Schema::make($livewire)->statePath('data'));
echo $schema->getComponents()[0]->toEmbeddedHtml();
