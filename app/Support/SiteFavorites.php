<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SiteFavorites
{
    private const REQUEST_CACHE_KEY = 'site.favorites.normalized';

    public static function ids(?Request $request = null): Collection
    {
        $request ??= request();

        /** @var Collection|null $cachedIds */
        $cachedIds = $request->attributes->get(self::REQUEST_CACHE_KEY);

        if ($cachedIds instanceof Collection) {
            return $cachedIds;
        }

        $ids = collect($request->session()->get('site.favorites', []))
            ->map(fn ($id): string => (string) $id)
            ->filter()
            ->unique()
            ->values();

        $request->attributes->set(self::REQUEST_CACHE_KEY, $ids);

        return $ids;
    }

    public static function all(?Request $request = null): array
    {
        return self::ids($request)->all();
    }

    public static function count(?Request $request = null): int
    {
        return self::ids($request)->count();
    }

    public static function contains(string $serviceId, ?Request $request = null): bool
    {
        return self::ids($request)->contains((string) $serviceId);
    }
}
