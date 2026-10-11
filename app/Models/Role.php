<?php

namespace App\Models;

use App\Services\Acl\RolePermission;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $root
 * @property string[] $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|User[] $users
 * @property int|null $users_count
 */
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    public const RESOURCE_NAME = 'role';

    public const ROOT_NAME = 'Root Administrator';

    public const ROOT_DESCRIPTION = 'Full access to every server and setting on the panel.';

    protected $table = 'roles';

    protected $fillable = ['name', 'description', 'permissions'];

    protected $casts = [
        'root' => 'boolean',
        'permissions' => 'array',
    ];

    protected $attributes = [
        'root' => false,
        'permissions' => '[]',
    ];

    public static array $validationRules = [
        'name' => 'required|string|between:1,191|unique:roles,name',
        'description' => 'nullable|string|max:191',
        'root' => 'boolean',
        'permissions' => 'array',
        'permissions.*' => 'string',
    ];

    protected static function booted(): void
    {
        // The root administrator role is part of the panel and can never be removed.
        static::deleting(fn (Role $role) => ! $role->root);
    }

    /**
     * Returns the built-in role held by every root administrator, creating it if
     * it has gone missing.
     */
    public static function root(): self
    {
        $role = static::query()->where('root', true)->first();
        if ($role === null) {
            $role = new self();
            $role->forceFill([
                'name' => self::ROOT_NAME,
                'description' => self::ROOT_DESCRIPTION,
                'root' => true,
            ])->save();
        }

        return $role;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Only ever store permissions that the panel actually knows about.
     */
    public function setPermissionsAttribute(?array $value): void
    {
        $this->attributes['permissions'] = json_encode(RolePermission::filter($value ?? []));
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}
