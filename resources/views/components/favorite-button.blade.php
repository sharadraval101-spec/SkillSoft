@props([
    'service',
    'size' => 'default',
])

@php
    $likedServiceIds = collect(session('site.favorites', []))
        ->map(fn ($id): string => (string) $id)
        ->filter()
        ->unique()
        ->all();

    $isLiked = in_array((string) $service->id, $likedServiceIds, true);

    $sizeMap = [
        'small' => 'h-10 w-10 rounded-[12px]',
        'default' => 'h-12 w-12 rounded-[10px]',
    ];

    $iconSizeMap = [
        'small' => 'h-4 w-4',
        'default' => 'h-5 w-5',
    ];
@endphp

<form method="POST" action="{{ route('site.favorites.toggle', $service) }}" class="inline-flex">
    @csrf
    <button
        type="submit"
        class="inline-flex {{ $sizeMap[$size] ?? $sizeMap['default'] }} items-center justify-center border transition {{ $isLiked ? 'border-rose-200 bg-rose-50 text-rose-500' : 'border-zinc-200 text-zinc-400 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-500' }}"
        aria-label="{{ $isLiked ? 'Remove from liked services' : 'Add to liked services' }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconSizeMap[$size] ?? $iconSizeMap['default'] }}" viewBox="0 0 24 24" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="m12 21-1.45-1.32C5.4 15.01 2 11.93 2 8.15 2 5.07 4.42 2.7 7.5 2.7c1.74 0 3.41.81 4.5 2.09A6 6 0 0 1 16.5 2.7C19.58 2.7 22 5.07 22 8.15c0 3.78-3.4 6.86-8.55 11.54L12 21Z"/>
        </svg>
    </button>
</form>
