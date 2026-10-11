<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Webhooks\Pages\CreateWebhook;
use App\Filament\Resources\Webhooks\Pages\EditWebhook;
use App\Filament\Resources\Webhooks\Pages\ListWebhooks;
use App\Filament\Resources\Webhooks\RelationManagers\DeliveriesRelationManager;
use App\Filament\Resources\Webhooks\WebhookResource;
use App\Jobs\Webhooks\DeliverWebhook;
use App\Models\Role;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class WebhooksTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        config()->set('webhooks.allow_private_endpoints', true);
        Webhook::forgetSubscriptions();
    }

    public function test_admin_can_create_and_edit_a_webhook(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));

        Livewire::test(CreateWebhook::class)
            ->fillForm(['name' => 'Billing', 'endpoint' => 'https://hooks.example.com/abc'])
            ->call('create')
            ->assertHasFormErrors(['subscriptions.resources'])
            ->assertNotified(trans('admin/webhooks.validation.events'));

        Livewire::test(CreateWebhook::class)
            ->fillForm([
                'name' => 'Billing',
                'endpoint' => 'ftp://hooks.example.com/abc',
                'headers' => ['Bad Name' => 'x'],
                'subscriptions.resources' => ['model:server.created'],
            ])
            ->call('create')
            ->assertHasFormErrors(['endpoint', 'headers']);

        Livewire::test(CreateWebhook::class)
            ->fillForm([
                'name' => 'Billing',
                'endpoint' => 'https://hooks.example.com/abc',
                'secret' => 'shh',
                'headers' => ['Authorization' => 'Bearer abc'],
                'subscriptions.resources' => ['model:server.created'],
                'subscriptions.auth' => ['auth:fail'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $webhook = Webhook::query()->sole();
        $this->assertSame(['model:server.created', 'auth:fail'], $webhook->events);
        $this->assertSame(Webhook::TYPE_STANDARD, $webhook->type);
        $this->assertSame('https://hooks.example.com/abc', $webhook->endpoint);
        $this->assertSame(['Authorization' => 'Bearer abc'], $webhook->headers);
        $this->assertActivityLogged('webhook:create');

        // Where it sends to and what it signs with are only ever stored encrypted.
        $stored = DB::table('webhooks')->first();
        $this->assertStringNotContainsString('hooks.example.com', $stored->endpoint);
        $this->assertStringNotContainsString('shh', $stored->secret);
        $this->assertStringNotContainsString('Bearer', $stored->headers);
        $this->assertArrayNotHasKey('endpoint', $webhook->toArray());

        Livewire::test(EditWebhook::class, ['record' => $webhook->id])
            ->assertFormSet([
                'endpoint' => 'https://hooks.example.com/abc',
                'secret' => 'shh',
                'subscriptions.auth' => ['auth:fail'],
            ])
            ->fillForm(['endpoint' => 'https://discord.com/api/webhooks/1/token'])
            ->assertFormSet(['type' => Webhook::TYPE_DISCORD, 'message.title' => '{{label}}'])
            ->fillForm(['subscriptions.auth' => [], 'subscriptions.admin' => ['webhook:update']])
            ->call('save')
            ->assertHasNoFormErrors();

        $webhook->refresh();
        $this->assertSame(Webhook::TYPE_DISCORD, $webhook->type);
        $this->assertSame(['model:server.created', 'webhook:update'], $webhook->events);
        $this->assertSame('{{summary}}', $webhook->message['description']);
        $this->assertSame([$webhook->id], Webhook::subscriptions()['webhook:update']);
        $this->assertActivityLogged('webhook:update');

        Livewire::test(ListWebhooks::class)
            ->assertCanSeeTableRecords([$webhook])
            ->callTableAction('replicate', $webhook)
            ->callTableAction('delete', $webhook);

        $copy = Webhook::query()->sole();
        $this->assertSame('Billing (copy)', $copy->name);
        $this->assertFalse($copy->enabled);
        $this->assertNotSame($webhook->uuid, $copy->uuid);
        $this->assertSame('https://discord.com/api/webhooks/1/token', $copy->endpoint);
        $this->assertActivityLogged('webhook:delete');
        $this->assertSame([], Webhook::subscriptions());
    }

    public function test_webhook_can_be_tested_and_deliveries_sent_again(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Http::fake(['*' => Http::sequence()->push('nope', 500)->push('ok', 200)]);
        Bus::fake([DeliverWebhook::class]);

        $webhook = Webhook::factory()->create();

        Livewire::test(EditWebhook::class, ['record' => $webhook->id])
            ->callAction('test')
            ->assertNotified(trans('admin/webhooks.notices.failed'))
            ->callAction('test')
            ->assertNotified(trans('admin/webhooks.notices.delivered'));

        $this->assertSame([false, true], WebhookDelivery::query()->orderBy('id')->pluck('successful')->all());
        $failed = WebhookDelivery::query()->orderBy('id')->first();

        Livewire::test(DeliveriesRelationManager::class, ['ownerRecord' => $webhook, 'pageClass' => EditWebhook::class])
            ->assertCanSeeTableRecords(WebhookDelivery::all())
            ->callTableAction('redeliver', $failed)
            ->assertNotified(trans('admin/webhooks.notices.redelivery_queued'));

        Bus::assertDispatched(DeliverWebhook::class, fn (DeliverWebhook $job) => $job->webhook === $webhook->id
            && $job->payload === $failed->payload
            && $job->uuid !== $failed->uuid);
    }

    public function test_role_holder_needs_the_webhooks_permission(): void
    {
        $webhook = Webhook::factory()->create();

        $this->actingAsRoleHolder(['locations.read']);
        $this->get(WebhookResource::getUrl())->assertForbidden();

        $this->actingAsRoleHolder(['webhooks.read']);
        $this->get(WebhookResource::getUrl())->assertOk();
        $this->assertFalse(WebhookResource::canCreate());
        $this->assertFalse(WebhookResource::canEdit($webhook));

        Livewire::test(ListWebhooks::class)
            ->assertTableActionHidden('test', $webhook)
            ->assertTableActionHidden('delete', $webhook);

        $this->actingAsRoleHolder(['webhooks.read', 'webhooks.update']);
        Livewire::test(ListWebhooks::class)
            ->assertTableActionVisible('test', $webhook)
            ->assertTableActionHidden('delete', $webhook);
    }

    private function actingAsRoleHolder(array $permissions): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['permissions' => $permissions]));
        $this->actingAs($user);

        return $user;
    }
}
