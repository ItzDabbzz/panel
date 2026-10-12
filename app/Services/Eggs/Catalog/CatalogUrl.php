<?php

namespace App\Services\Eggs\Catalog;

final class CatalogUrl
{
    public const REPOSITORIES = ['reviactyl/eggs', 'pterodactyl/game-eggs', 'pterodactyl/application-eggs', 'pterodactyl/generic-eggs'];

    public static function path(string $path): bool
    {
        if ($path === '' || preg_match('/[\\\\%\x00-\x1f\x7f]/', $path)) {
            return false;
        }
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return false;
            }
        }

        return true;
    }

    public static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    public static function validate(string $url, string $repository, string $path): string
    {
        $parts = parse_url($url);
        if (! in_array($repository, self::REPOSITORIES, true) || ! self::path($path) || ! is_array($parts)
            || ($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== 'raw.githubusercontent.com'
            || array_intersect(['user', 'pass', 'port', 'query', 'fragment'], array_keys($parts)) !== []) {
            throw new CatalogException('url');
        }
        $segments = explode('/', ltrim($parts['path'] ?? '', '/'));
        if (count($segments) < 4 || implode('/', array_slice($segments, 0, 2)) !== $repository
            || ! preg_match('/^[a-zA-Z0-9_-][a-zA-Z0-9._-]*$/D', $segments[2]) || in_array($segments[2], ['.', '..'], true)) {
            throw new CatalogException('url');
        }
        $remotePath = implode('/', array_slice($segments, 3));
        // Only canonical segment encoding is accepted; encoded separators never become path boundaries.
        if ($remotePath !== $path && $remotePath !== self::encodePath($path)) {
            throw new CatalogException('url');
        }

        return 'https://raw.githubusercontent.com/'.$repository.'/'.$segments[2].'/'.self::encodePath($path);
    }
}
