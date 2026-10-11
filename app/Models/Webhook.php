<?php

namespace App\Models;

use Database\Factories\WebhookFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property bool $enabled
 * @property string $type
 * @property string $endpoint
 * @property string|null $secret
 * @property array<string, string>|null $headers
 * @property string[] $events
 * @property array<string, mixed>|null $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|WebhookDelivery[] $deliveries
 * @property WebhookDelivery|null $latestDelivery
 */
class Webhook extends Model
{
    /** @use HasFactory<WebhookFactory> */
    use HasFactory;

    public const RESOURCE_NAME = 'webhook';

    public const TYPE_STANDARD = 'standard';

    public const TYPE_DISCORD = 'discord';

    public const TYPES = [self::TYPE_STANDARD, self::TYPE_DISCORD];

    public const HEADER_NAME_PATTERN = '/^[A-Za-z0-9!#$%&\'*+.^_`|~-]+$/';

    /**
     * Headers the panel sets itself on every delivery and that cannot be replaced.
     */
    public const RESERVED_HEADERS = ['content-type', 'content-length', 'host', 'user-agent'];

    private const SUBSCRIPTIONS_CACHE_KEY = 'webhooks:subscriptions';

    protected $table = 'webhooks';

    protected $guarded = ['id', 'uuid', 'created_at', 'updated_at'];

    protected $hidden = ['endpoint', 'secret', 'headers'];

    protected $attributes = [
        'enabled' => true,
        'type' => self::TYPE_STANDARD,
    ];

    protected $casts = [
        'enabled' => 'bool',
        'endpoint' => 'encrypted',
        'secret' => 'encrypted',
        'headers' => 'encrypted:array',
        'events' => 'array',
        'message' => 'array',
    ];

    public static array $validationRules = [
        'name' => ['required', 'string', 'max:191'],
        'description' => ['nullable', 'string', 'max:191'],
        'enabled' => ['boolean'],
        'type' => ['required', 'string', 'in:standard,discord'],
        'endpoint' => ['required', 'string', 'max:2048', 'url:http,https'],
        'secret' => ['nullable', 'string', 'max:191'],
        'headers' => ['nullable', 'array', 'max:20'],
        'headers.*' => ['nullable', 'string', 'max:2048', 'not_regex:/[\r\n]/'],
        'events' => ['required', 'array', 'min:1'],
        'events.*' => ['string', 'distinct', 'max:191'],
        'message' => ['nullable', 'array'],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Webhook $webhook) {
            $webhook->uuid ??= Str::uuid()->toString();
        });

        static::saved(fn () => static::forgetSubscriptions());
        static::deleted(fn () => static::forgetSubscriptions());
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * @return HasMany<WebhookDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    /**
     * @return HasOne<WebhookDelivery, $this>
     */
    public function latestDelivery(): HasOne
    {
        return $this->hasOne(WebhookDelivery::class)->latestOfMany();
    }

    /**
     * Returns the events that enabled webhooks are listening for, along with the IDs of
     * the webhooks interested in each. This is consulted every time something happens on
     * the panel, so it is cached until a webhook changes.
     *
     * @return array<string, int[]>
     */
    public static function subscriptions(): array
    {
        return Cache::rememberForever(self::SUBSCRIPTIONS_CACHE_KEY, function (): array {
            $subscriptions = [];
            foreach (static::query()->where('enabled', true)->get(['id', 'events']) as $webhook) {
                foreach ($webhook->events ?? [] as $event) {
                    $subscriptions[$event][] = $webhook->id;
                }
            }

            return $subscriptions;
        });
    }

    public static function forgetSubscriptions(): void
    {
        Cache::forget(self::SUBSCRIPTIONS_CACHE_KEY);
    }

    public function endpointHost(): string
    {
        return (string) parse_url($this->endpoint, PHP_URL_HOST);
    }

    public function isDiscord(): bool
    {
        return $this->type === self::TYPE_DISCORD;
    }

    public static function typeForEndpoint(?string $endpoint): string
    {
        $host = strtolower((string) parse_url((string) $endpoint, PHP_URL_HOST));
        $path = (string) parse_url((string) $endpoint, PHP_URL_PATH);

        $discord = in_array($host, ['discord.com', 'discordapp.com'], true) || Str::endsWith($host, ['.discord.com', '.discordapp.com']);

        return $discord && Str::startsWith($path, '/api/webhooks/') ? self::TYPE_DISCORD : self::TYPE_STANDARD;
    }
}
