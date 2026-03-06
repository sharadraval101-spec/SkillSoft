@props([
    'categories' => collect(),
    'locations' => collect(),
    'filters' => [],
    'sortOptions' => [],
    'formId' => 'serviceFilters',
])

<aside class="customer-surface h-fit p-5 lg:sticky lg:top-24">
    <div class="mb-5 flex items-center justify-between border-b border-sky-100 pb-4">
        <h2 class="text-base font-bold text-sky-900">Filters</h2>
        <button type="button" data-clear-target="{{ $formId }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Clear all</button>
    </div>

    <form id="{{ $formId }}" method="GET" action="{{ route('site.services.index') }}" class="space-y-4">
        <div>
            <label for="q" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Search</label>
            <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Service or provider"
                class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
        </div>

        <div>
            <label for="category" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Category</label>
            <select id="category" name="category" class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="price_min" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Min Price</label>
                <input id="price_min" name="price_min" type="number" min="0" step="0.01" value="{{ $filters['price_min'] ?? '' }}"
                    class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
            </div>
            <div>
                <label for="price_max" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Max Price</label>
                <input id="price_max" name="price_max" type="number" min="0" step="0.01" value="{{ $filters['price_max'] ?? '' }}"
                    class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
            </div>
        </div>

        <div>
            <label for="rating" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Rating</label>
            <select id="rating" name="rating" class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                <option value="">Any rating</option>
                @foreach([5,4,3,2,1] as $ratingOption)
                    <option value="{{ $ratingOption }}" @selected((string) ($filters['rating'] ?? '') === (string) $ratingOption)>
                        {{ $ratingOption }} stars & above
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="location" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Location</label>
            <select id="location" name="location" class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                <option value="">All locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location }}" @selected(($filters['location'] ?? '') === $location)>{{ $location }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="availability" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Availability</label>
            <input id="availability" name="availability" type="date" value="{{ $filters['availability'] ?? '' }}"
                class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
        </div>

        <div>
            <label for="sort" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Sort</label>
            <select id="sort" name="sort" class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                @foreach($sortOptions as $sortKey => $sortLabel)
                    <option value="{{ $sortKey }}" @selected(($filters['sort'] ?? 'recommended') === $sortKey)>{{ $sortLabel }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="w-full rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500">
            Apply Filters
        </button>
    </form>
</aside>
