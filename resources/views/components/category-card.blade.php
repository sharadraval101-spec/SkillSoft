@props(['category'])

<a href="{{ route('site.services.index', ['category' => $category->slug]) }}" class="group customer-surface block p-5 transition duration-200 hover:-translate-y-1 hover:border-sky-200 hover:shadow-xl hover:shadow-sky-100">
    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h10M4 17h7"/>
        </svg>
    </span>
    <h3 class="mt-4 text-base font-bold text-sky-950 group-hover:text-sky-700">{{ $category->name }}</h3>
    <p class="mt-2 text-sm leading-6 text-sky-700 line-clamp-2">
        {{ $category->description ?: 'Find top-rated professionals in this category near you.' }}
    </p>
</a>
