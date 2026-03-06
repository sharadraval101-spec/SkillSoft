@props(['review'])

<article class="customer-surface p-5">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-bold text-sky-900">{{ $review->customer?->name ?? 'Customer' }}</p>
            <p class="text-xs text-sky-600">{{ $review->service?->name ?? 'Service' }}</p>
        </div>
        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
            {{ str_repeat('★', (int) $review->rating) }}
        </span>
    </div>
    @if($review->title)
        <h4 class="mt-4 text-sm font-semibold text-sky-900">{{ $review->title }}</h4>
    @endif
    <p class="mt-2 text-sm leading-6 text-sky-700">{{ $review->comment ?: 'Great experience and smooth booking process.' }}</p>
</article>
