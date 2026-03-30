<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CustomerFavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $likedServiceIds = collect($request->session()->get('site.favorites', []))
            ->map(fn ($id): string => (string) $id)
            ->filter()
            ->unique()
            ->values();

        $services = Service::query()
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
            ->sortBy(fn (Service $service) => array_search((string) $service->id, $likedServiceIds->all(), true))
            ->values();

        $services = $this->decorateServicesForUi($services);

        return view('site.favorites.index', [
            'services' => $services,
            'likedCount' => $likedServiceIds->count(),
        ]);
    }

    public function toggle(Request $request, Service $service): RedirectResponse
    {
        $likedServiceIds = collect($request->session()->get('site.favorites', []))
            ->map(fn ($id): string => (string) $id)
            ->filter()
            ->unique()
            ->values();

        $serviceId = (string) $service->id;

        if ($likedServiceIds->contains($serviceId)) {
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

        return back();
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
