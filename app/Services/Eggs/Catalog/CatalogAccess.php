<?php

namespace App\Services\Eggs\Catalog;

use App\Filament\AdminAccess;
use App\Filament\Resources\Nests\EggResource;
use App\Filament\Resources\Nests\NestResource;
use App\Models\Nest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;

class CatalogAccess
{
    public function authorize(bool $import = false): User
    {
        $id = AdminAccess::user()?->id;
        $user = $id ? User::query()->find($id) : null;
        abort_unless($user && $user->isAdmin(), 403);
        Filament::auth()->setUser($user);
        abort_unless(EggResource::canViewAny() && (! $import || EggResource::canCreate()), 403);

        return $user;
    }

    public function nests(): Collection
    {
        $this->authorize();
        if (! NestResource::canViewAny()) {
            return collect();
        }

        return Nest::query()->orderBy('name')->get()->filter(fn (Nest $nest) => NestResource::canView($nest));
    }

    public function nest(int $id): Nest
    {
        $this->authorize(true);
        $nest = Nest::query()->find($id);
        if (! $nest) {
            throw new CatalogException('nest');
        }
        abort_unless(NestResource::canViewAny() && NestResource::canView($nest), 403);

        return $nest;
    }
}
