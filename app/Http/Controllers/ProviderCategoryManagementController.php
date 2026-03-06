<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProviderCategoryManagementController extends Controller
{
    public function index(): View
    {
        return view('provider.categories.index', [
            'categoriesDataUrl' => route('provider.categories.data'),
        ]);
    }

    public function data(): JsonResponse
    {
        $categories = ServiceCategory::query()
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => $categories->map(fn (ServiceCategory $category): array => $this->toDataRow($category)),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('service_categories', 'name')],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $status = $validated['status'];
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('service-categories', 'public')
            : null;

        ServiceCategory::query()->create([
            'name' => $validated['name'],
            'slug' => $this->makeUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'status' => $status,
            'is_active' => $status === 'active',
            'image_path' => $imagePath,
        ]);

        return $this->successResponse($request, 'Category created successfully.', 201);
    }

    public function update(Request $request, ServiceCategory $serviceCategory): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories', 'name')->ignore($serviceCategory->id),
            ],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $status = $validated['status'];
        $serviceCategory->name = $validated['name'];
        $serviceCategory->slug = $this->makeUniqueSlug($validated['name'], $serviceCategory->id);
        $serviceCategory->description = $validated['description'] ?? null;
        $serviceCategory->status = $status;
        $serviceCategory->is_active = $status === 'active';

        if ($request->hasFile('image')) {
            if ($serviceCategory->image_path) {
                Storage::disk('public')->delete($serviceCategory->image_path);
            }

            $serviceCategory->image_path = $request->file('image')->store('service-categories', 'public');
        }

        $serviceCategory->save();

        return $this->successResponse($request, 'Category updated successfully.');
    }

    public function toggleStatus(Request $request, ServiceCategory $serviceCategory): JsonResponse|RedirectResponse
    {
        $serviceCategory->status = $serviceCategory->status === 'active' ? 'inactive' : 'active';
        $serviceCategory->is_active = $serviceCategory->status === 'active';
        $serviceCategory->save();

        $message = $serviceCategory->status === 'active'
            ? 'Category marked as active.'
            : 'Category marked as inactive.';

        return $this->successResponse($request, $message);
    }

    public function destroy(Request $request, ServiceCategory $serviceCategory): JsonResponse|RedirectResponse
    {
        try {
            if ($serviceCategory->image_path) {
                Storage::disk('public')->delete($serviceCategory->image_path);
            }

            $serviceCategory->delete();
        } catch (QueryException) {
            return $this->errorResponse(
                $request,
                'This category is linked to existing services and cannot be deleted.',
                422
            );
        }

        return $this->successResponse($request, 'Category deleted successfully.');
    }

    private function toDataRow(ServiceCategory $category): array
    {
        $status = $category->status ?: ($category->is_active ? 'active' : 'inactive');

        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description ? Str::limit($category->description, 100) : '-',
            'full_description' => $category->description,
            'status' => $status,
            'status_label' => ucfirst($status),
            'image_url' => $category->image_url,
            'created_at' => $category->created_at?->format('d M Y, h:i A') ?? '-',
            'created_at_timestamp' => $category->created_at?->timestamp ?? 0,
            'update_url' => route('provider.categories.update', $category),
            'toggle_status_url' => route('provider.categories.toggle-status', $category),
            'delete_url' => route('provider.categories.destroy', $category),
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
            ServiceCategory::query()
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
