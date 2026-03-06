<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProviderServiceManagementController extends Controller
{
    public function index(Request $request): View
    {
        $categories = ServiceCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'is_active']);

        return view('provider.services.index', [
            'servicesDataUrl' => route('provider.services.data'),
            'categories' => $categories,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $providerProfile = $this->resolveProviderProfile($request);

        $services = Service::query()
            ->with('category:id,name')
            ->where('provider_profile_id', $providerProfile->id)
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => $services->map(fn (Service $service): array => $this->toDataRow($service)),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $providerProfile = $this->resolveProviderProfile($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')->where(fn ($query) => $query->where('provider_profile_id', $providerProfile->id)),
            ],
            'category_id' => ['required', Rule::exists('service_categories', 'id')],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1|max:10080',
            'type' => ['required', Rule::in(['1-on-1', 'group'])],
            'max_customers' => 'nullable|integer|min:2|required_if:type,group',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $status = $validated['status'];
        $type = $validated['type'];
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('services', 'public')
            : null;

        Service::query()->create([
            'provider_profile_id' => $providerProfile->id,
            'service_category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $this->makeUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'base_price' => $validated['price'],
            'duration_minutes' => (int) $validated['duration_minutes'],
            'type' => $type,
            'max_customers' => $type === 'group' ? (int) $validated['max_customers'] : null,
            'status' => $status,
            'is_active' => $status === 'active',
        ]);

        return $this->successResponse($request, 'Service created successfully.', 201);
    }

    public function update(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        $providerProfile = $this->resolveProviderProfile($request);
        $this->ensureServiceOwnership($service, $providerProfile);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')
                    ->where(fn ($query) => $query->where('provider_profile_id', $providerProfile->id))
                    ->ignore($service->id),
            ],
            'category_id' => ['required', Rule::exists('service_categories', 'id')],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1|max:10080',
            'type' => ['required', Rule::in(['1-on-1', 'group'])],
            'max_customers' => 'nullable|integer|min:2|required_if:type,group',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $status = $validated['status'];
        $type = $validated['type'];

        $service->name = $validated['name'];
        $service->slug = $this->makeUniqueSlug($validated['name'], $service->id);
        $service->service_category_id = $validated['category_id'];
        $service->description = $validated['description'] ?? null;
        $service->base_price = $validated['price'];
        $service->duration_minutes = (int) $validated['duration_minutes'];
        $service->type = $type;
        $service->max_customers = $type === 'group' ? (int) $validated['max_customers'] : null;
        $service->status = $status;
        $service->is_active = $status === 'active';

        if ($request->hasFile('image')) {
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }

            $service->image_path = $request->file('image')->store('services', 'public');
        }

        $service->save();

        return $this->successResponse($request, 'Service updated successfully.');
    }

    public function toggleStatus(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        $providerProfile = $this->resolveProviderProfile($request);
        $this->ensureServiceOwnership($service, $providerProfile);

        $service->status = $service->status === 'active' ? 'inactive' : 'active';
        $service->is_active = $service->status === 'active';
        $service->save();

        $message = $service->status === 'active'
            ? 'Service marked as active.'
            : 'Service marked as inactive.';

        return $this->successResponse($request, $message);
    }

    public function destroy(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        $providerProfile = $this->resolveProviderProfile($request);
        $this->ensureServiceOwnership($service, $providerProfile);

        try {
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }

            $service->delete();
        } catch (QueryException) {
            return $this->errorResponse(
                $request,
                'This service is linked to existing bookings and cannot be deleted.',
                422
            );
        }

        return $this->successResponse($request, 'Service deleted successfully.');
    }

    private function ensureServiceOwnership(Service $service, ProviderProfile $providerProfile): void
    {
        abort_unless($service->provider_profile_id === $providerProfile->id, 403);
    }

    private function resolveProviderProfile(Request $request): ProviderProfile
    {
        /** @var User $user */
        $user = $request->user();

        return ProviderProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $user->name.' Services',
                'status' => 'active',
                'verified_at' => now(),
            ]
        );
    }

    private function toDataRow(Service $service): array
    {
        $status = $service->status ?: ($service->is_active ? 'active' : 'inactive');
        $type = $service->type ?: '1-on-1';
        $maxCustomers = $type === 'group' ? ($service->max_customers ?: 0) : null;

        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description ? Str::limit($service->description, 100) : '-',
            'full_description' => $service->description,
            'category_id' => $service->service_category_id,
            'category_name' => $service->category?->name ?? 'N/A',
            'price' => number_format((float) ($service->base_price ?? 0), 2),
            'price_value' => (float) ($service->base_price ?? 0),
            'duration_minutes' => (int) ($service->duration_minutes ?? 0),
            'type' => $type,
            'max_customers' => $maxCustomers,
            'max_customers_label' => $maxCustomers ?: '-',
            'status' => $status,
            'status_label' => ucfirst($status),
            'image_url' => $service->image_url,
            'created_at' => $service->created_at?->format('d M Y, h:i A') ?? '-',
            'created_at_timestamp' => $service->created_at?->timestamp ?? 0,
            'update_url' => route('provider.services.update', $service),
            'toggle_status_url' => route('provider.services.toggle-status', $service),
            'delete_url' => route('provider.services.destroy', $service),
        ];
    }

    private function makeUniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = Str::lower(Str::random(8));
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Service::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function successResponse(
        Request $request,
        string $message,
        int $status = 200
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('success', $message);
    }

    private function errorResponse(
        Request $request,
        string $message,
        int $status = 422
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
