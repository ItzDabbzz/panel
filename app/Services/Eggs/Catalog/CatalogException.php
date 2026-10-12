<?php

namespace App\Services\Eggs\Catalog;

class CatalogException extends \RuntimeException
{
    public function __construct(public readonly string $reason = 'unavailable', public readonly int $retryAfter = 60)
    {
        parent::__construct(trans('admin/catalog.errors.'.$reason));
    }
}
