<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\SiteFavorites;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CustomerFavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $likedServiceIds = SiteFavorites::ids($request);
        $favoriteSortOrder = array_flip($likedServiceIds->all());

        $services = Service::query()
            ->select([
                'id',
                'provider_profile_id',
                'service_category_id',
                'branch_id',
                'name',
                'slug',
                'description',
                'image_path',
                'duration_minutes',
                'type',
                'base_price',
                'created_at',
            ])
            ->with([
                'category:id,name,slug',
                'branch:id,name,city,state',
                'providerProfile.user:id,name,profile_photo_path',
            ])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->when(
                $likedServiceIds->isNotEmpty(),
                fn ($query) => $query->whereIn('id', $likedServiceIds->all()),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->get()
            ->sortBy(fn (Service $service) => $favoriteSortOrder[(string) $service->id] ?? PHP_INT_MAX)
            ->values();

        $services = $this->decorateServicesForUi($services);

        return view('site.favorites.index', [
            'services' => $services,
            'likedCount' => $likedServiceIds->count(),
        ]);
    }

    public function toggle(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        $likedServiceIds = SiteFavorites::ids($request);

        $serviceId = (string) $service->id;
        $wasLiked = $likedServiceIds->contains($serviceId);

        if ($wasLiked) {
            $likedServiceIds = $likedServiceIds
                ->reject(fn (string $id): bool => $id === $serviceId)
                ->values();
        } else {
            $likedServiceIds = $likedServiceIds
                ->prepend($serviceId)
                ->unique()
                ->values();
        }

        $request->session()->put('site.favorites', $likedServiceIds->all());

        $isLiked = !$wasLiked;
        $message = $isLiked
            ? 'Service added to liked services.'
            : 'Service removed from liked services.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'data' => [
                    'service_id' => $serviceId,
                    'liked' => $isLiked,
                    'liked_count' => $likedServiceIds->count(),
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    private function decorateServicesForUi(Collection $services): Collection
    {
        return $services->map(function (Service $service): Service {
            $gallery = $this->generateServiceGallery($service);
            $service->setAttribute('ui_image', $gallery[0] ?? null);
            $service->setAttribute('ui_gallery', $gallery);
            $service->setAttribute('avg_rating', round((float) ($service->avg_rating ?? 0), 1));

            return $service;
        });
    }

    private function generateServiceGallery(Service $service): array
    {
        $seed = md5($service->id.'|'.$service->name);
        $fallbackImages = collect(range(0, 3))
            ->map(function (int $index) use ($seed): string {
                $segment = substr($seed, $index * 8, 8);

                return 'https://picsum.photos/seed/'.$segment.'/1200/800';
            })
            ->all();

        $images = [];
        if ($service->image_url) {
            $images[] = $service->image_url;
        }

        return array_values(array_unique(array_merge($images, $fallbackImages)));
    }
}
