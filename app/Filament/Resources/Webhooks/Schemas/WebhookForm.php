<?php

namespace App\Filament\Resources\Webhooks\Schemas;

use App\Models\Webhook;
use App\Services\Webhooks\DiscordMessage;
use App\Services\Webhooks\WebhookEvents;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WebhookForm
{
    public const EVENTS_KEY = 'subscriptions';

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('admin/webhooks.sections.details'))
                ->description(trans('admin/webhooks.sections.details_description'))
                ->icon('tabler-webhook')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(trans('admin/webhooks.fields.name'))
                        ->placeholder(trans('admin/webhooks.placeholders.name'))
                        ->required()
                        ->maxLength(191),

                    TextInput::make('description')
                        ->label(trans('admin/webhooks.fields.description'))
                        ->placeholder(trans('admin/webhooks.placeholders.description'))
                        ->maxLength(191),

                    Toggle::make('enabled')
                        ->label(trans('admin/webhooks.fields.enabled'))
                        ->default(true),
                ]),

            Section::make(trans('admin/webhooks.sections.delivery'))
                ->description(trans('admin/webhooks.sections.delivery_description'))
                ->icon('tabler-send')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('endpoint')
                        ->label(trans('admin/webhooks.fields.endpoint'))
                        ->placeholder('https://example.com/webhooks/panel')
                        ->required()
                        ->maxLength(2048)
                        ->rule('url:http,https')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            if (Webhook::typeForEndpoint($state) === Webhook::TYPE_DISCORD) {
                                $set('type', Webhook::TYPE_DISCORD);
                                self::startMessage($get, $set);
                            }
                        }),

                    Select::make('type')
                        ->label(trans('admin/webhooks.fields.type'))
                        ->options(self::typeOptions())
                        ->default(Webhook::TYPE_STANDARD)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            if ($state === Webhook::TYPE_DISCORD) {
                                self::startMessage($get, $set);
                            }
                        }),

                    TextInput::make('secret')
                        ->label(trans('admin/webhooks.fields.secret'))
                        ->helperText(trans('admin/webhooks.helpers.secret'))
                        ->password()
                        ->revealable()
                        ->autocomplete('off')
                        ->maxLength(191)
                        ->suffixAction(
                            Action::make('generateSecret')
                                ->label(trans('admin/webhooks.actions.generate_secret'))
                                ->icon('tabler-refresh')
                                ->action(fn (Set $set) => $set('secret', Str::random(40)))
                        )
                        ->visible(fn (Get $get): bool => $get('type') !== Webhook::TYPE_DISCORD)
                        ->columnSpanFull(),

                    KeyValue::make('headers')
                        ->label(trans('admin/webhooks.fields.headers'))
                        ->keyLabel(trans('admin/webhooks.fields.header_name'))
                        ->valueLabel(trans('admin/webhooks.fields.header_value'))
                        ->keyPlaceholder('Authorization')
                        ->addActionLabel(trans('admin/webhooks.actions.add_header'))
                        ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                            foreach (array_keys(Arr::wrap($value)) as $name) {
                                if (! preg_match(Webhook::HEADER_NAME_PATTERN, (string) $name)) {
                                    $fail(trans('admin/webhooks.validation.header_name', ['name' => $name]));
                                } elseif (in_array(strtolower((string) $name), Webhook::RESERVED_HEADERS, true)) {
                                    $fail(trans('admin/webhooks.validation.header_reserved', ['name' => $name]));
                                }
                            }
                        })
                        ->visible(fn (Get $get): bool => $get('type') !== Webhook::TYPE_DISCORD)
                        ->columnSpanFull(),
                ]),

            Section::make(trans('admin/webhooks.sections.message'))
                ->description(trans('admin/webhooks.sections.message_description'))
                ->icon('tabler-brand-discord')
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get): bool => $get('type') === Webhook::TYPE_DISCORD)
                ->schema([
                    TextInput::make('message.username')
                        ->label(trans('admin/webhooks.fields.message_username'))
                        ->maxLength(80),

                    TextInput::make('message.avatar_url')
                        ->label(trans('admin/webhooks.fields.message_avatar'))
                        ->rule('nullable')
                        ->rule('url:http,https')
                        ->maxLength(2048),

                    Textarea::make('message.content')
                        ->label(trans('admin/webhooks.fields.message_content'))
                        ->rows(2)
                        ->maxLength(2000)
                        ->columnSpanFull(),

                    TextInput::make('message.title')
                        ->label(trans('admin/webhooks.fields.message_title'))
                        ->default(DiscordMessage::DEFAULTS['title'])
                        ->maxLength(256),

                    ColorPicker::make('message.color')
                        ->label(trans('admin/webhooks.fields.message_color'))
                        ->default(DiscordMessage::DEFAULTS['color'])
                        ->regex('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'),

                    Textarea::make('message.description')
                        ->label(trans('admin/webhooks.fields.message_description'))
                        ->helperText(trans('admin/webhooks.helpers.message_variables'))
                        ->default(DiscordMessage::DEFAULTS['description'])
                        ->rows(4)
                        ->maxLength(4096)
                        ->columnSpanFull(),

                    TextInput::make('message.footer')
                        ->label(trans('admin/webhooks.fields.message_footer'))
                        ->default(DiscordMessage::DEFAULTS['footer'])
                        ->maxLength(2048),

                    Toggle::make('message.timestamp')
                        ->label(trans('admin/webhooks.fields.message_timestamp'))
                        ->default(DiscordMessage::DEFAULTS['timestamp'])
                        ->inline(false),
                ]),

            Section::make(trans('admin/webhooks.sections.events'))
                ->description(trans('admin/webhooks.sections.events_description'))
                ->icon('tabler-bolt')
                ->columnSpanFull()
                ->headerActions([self::toggleAllAction()])
                ->schema(array_map(
                    fn (string $group): Section => self::eventGroup($group),
                    array_keys(WebhookEvents::groups()),
                )),
        ]);
    }

    /**
     * Splits a stored list of events into the per-group checklists used by the form.
     *
     * @param  string[]  $events
     * @return array<string, string[]>
     */
    public static function toFormState(array $events): array
    {
        return array_map(
            fn (array $group): array => array_values(array_intersect($group, $events)),
            WebhookEvents::groups(),
        );
    }

    /**
     * Turns the per-group checklists back into a list of events.
     *
     * @return string[]
     */
    public static function fromFormState(array $state): array
    {
        return WebhookEvents::filter(Arr::flatten($state));
    }

    /**
     * Prepares what the form holds for being filled into a webhook.
     *
     * @throws ValidationException
     */
    public static function mutateBeforeSave(array $data): array
    {
        $data['events'] = self::fromFormState($data[self::EVENTS_KEY] ?? []);
        unset($data[self::EVENTS_KEY]);

        if ($data['events'] === []) {
            // Every group is closed when nothing is chosen, so the error under the field is out of sight.
            Notification::make()->title(trans('admin/webhooks.validation.events'))->danger()->send();

            throw ValidationException::withMessages([
                'data.'.self::EVENTS_KEY.'.'.WebhookEvents::GROUP_RESOURCES => trans('admin/webhooks.validation.events'),
            ]);
        }

        return $data;
    }

    public static function mutateBeforeFill(array $data): array
    {
        $data[self::EVENTS_KEY] = self::toFormState($data['events'] ?? []);
        unset($data['events']);

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return collect(Webhook::TYPES)
            ->mapWithKeys(fn (string $type) => [$type => trans("admin/webhooks.types.$type")])
            ->all();
    }

    private static function startMessage(Get $get, Set $set): void
    {
        $written = array_filter(Arr::wrap($get('message')), fn ($value) => is_string($value) && trim($value) !== '');

        if ($written === []) {
            $set('message', DiscordMessage::DEFAULTS);
        }
    }

    private static function toggleAllAction(): Action
    {
        $complete = function (Get $get): bool {
            foreach (WebhookEvents::groups() as $group => $events) {
                if (array_diff($events, $get(self::EVENTS_KEY.'.'.$group) ?? []) !== []) {
                    return false;
                }
            }

            return true;
        };

        return Action::make('toggleAllEvents')
            ->label(fn (Get $get): string => trans($complete($get) ? 'admin/webhooks.actions.deselect_all' : 'admin/webhooks.actions.select_all'))
            ->icon(fn (Get $get): string => $complete($get) ? 'tabler-square-x' : 'tabler-checks')
            ->color('gray')
            ->size('sm')
            ->action(function (Get $get, Set $set) use ($complete): void {
                $clear = $complete($get);
                foreach (WebhookEvents::groups() as $group => $events) {
                    $set(self::EVENTS_KEY.'.'.$group, $clear ? [] : $events);
                }
            });
    }

    private static function eventGroup(string $group): Section
    {
        $events = WebhookEvents::groups()[$group];
        $activity = $group !== WebhookEvents::GROUP_RESOURCES;
        $field = self::EVENTS_KEY.'.'.$group;

        return Section::make(trans("admin/webhooks.groups.$group"))
            ->compact()
            ->collapsible()
            // There are a lot of these, so only the groups already in use start out open.
            ->collapsed(fn (Get $get): bool => empty($get($field)))
            ->schema([
                CheckboxList::make($field)
                    ->hiddenLabel()
                    ->options(array_combine($events, array_map(fn (string $event): string => WebhookEvents::label($event), $events)))
                    ->descriptions($activity ? array_combine($events, $events) : [])
                    ->bulkToggleable(count($events) > 1)
                    ->columns(['md' => 2, 'xl' => 3])
                    ->gridDirection(GridDirection::Row),
            ]);
    }
}
