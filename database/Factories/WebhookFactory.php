<?php

namespace Database\Factories;

use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'name' => $this->faker->unique()->words(3, true),
            'enabled' => true,
            'type' => Webhook::TYPE_STANDARD,
            'endpoint' => 'https://hooks.example.com/'.Str::random(12),
            'events' => ['model:server.created'],
        ];
    }
}
