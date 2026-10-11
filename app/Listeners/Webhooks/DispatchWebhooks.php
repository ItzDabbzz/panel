<?php

namespace App\Listeners\Webhooks;

use App\Events\ActivityLogged;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispatchWebhooks
{
    public function __construct(private WebhookDispatcher $dispatcher) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(ActivityLogged::class, [self::class, 'activity']);
        $events->listen(['eloquent.created: *', 'eloquent.updated: *', 'eloquent.deleted: *'], [self::class, 'model']);
    }

    public function activity(ActivityLogged $event): void
    {
        // The entry is announced the moment it is saved, before what it is about has been
        // attached to it, so wait for the rest of it to be written.
        DB::afterCommit(fn () => $this->guarded(fn () => $this->dispatcher->activity($event->model)));
    }

    /**
     * @param  array<int, mixed>  $data
     */
    public function model(string $event, array $data): void
    {
        $model = $data[0] ?? null;
        if (! $model instanceof Model) {
            return;
        }

        $this->guarded(fn () => $this->dispatcher->model(Str::between($event, 'eloquent.', ':'), $model));
    }

    private function guarded(\Closure $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
