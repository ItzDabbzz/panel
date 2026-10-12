<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);
Auth::setUser(User::factory()->create(['root_admin' => true]));

$response = $app->make(Kernel::class)->handle(Request::create('http://panel.test/admin/designify'));
if ($response->getStatusCode() !== 200) {
    fwrite(STDERR, 'Designify fixture returned HTTP '.$response->getStatusCode());
    exit(1);
}

echo $response->getContent();
