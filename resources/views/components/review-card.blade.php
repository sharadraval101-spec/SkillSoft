@props(['review'])

<article class="rounded-[24px] border border-zinc-200 bg-zinc-50 px-5 py-5" data-motion-card>
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-zinc-950">{{ $review->customer?->name ?? 'Customer' }}</p>
            <p class="text-xs text-zinc-500">{{ $review->service?->name ?? 'Service' }}</p>
        </div>
        <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-semibold text-zinc-700">
            @for($i = 0; $i < (int) $review->rating; $i++)
                <span aria-hidden="true">&#9733;</span>
            @endfor
        </span>
    </div>
    @if($review->title)
        <h4 class="mt-4 text-sm font-semibold text-zinc-950">{{ $review->title }}</h4>
    @endif
    <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $review->comment ?: 'Great experience and smooth booking process.' }}</p>
</article>
