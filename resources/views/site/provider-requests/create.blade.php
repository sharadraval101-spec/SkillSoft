@extends('layouts.auth-user', ['title' => 'Become a Provider | SkillSlot'])

@section('content')
<section class="mx-auto max-w-[1160px]" data-motion-section>
    @if(session('success'))
        <div class="mb-6 rounded-[22px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-[22px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.02fr)_minmax(28rem,0.98fr)] lg:items-start">
        <div class="max-w-2xl">
            <a href="{{ route('site.home') }}" class="inline-flex shrink-0 items-center gap-3 text-zinc-950 leading-none" data-motion-kicker data-motion-action>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl">
                    <svg viewBox="0 0 56 52" class="block h-9 w-9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M8 42V10l16 8 16-8v32l-16-8-16 8Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M24 16c0-3.866 3.134-7 7-7s7 3.134 7 7c0 5.044-7 11-7 11s-7-5.956-7-11Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="31" cy="16" r="2.5" fill="currentColor"/>
                    </svg>
                </span>
                <span class="self-center text-lg font-semibold tracking-[-0.03em] leading-none">SkillSlot</span>
            </a>

            <p class="mt-8 text-sm font-medium uppercase tracking-[0.22em] text-zinc-400" data-motion-kicker>Become a Provider</p>
            <h1 class="mt-4 text-[2.9rem] font-semibold leading-[1.08] tracking-[-0.05em] text-zinc-950 sm:text-[3.7rem]" data-motion-title>
                Apply once, get reviewed, and receive a provider account after approval
            </h1>
            <p class="mt-5 max-w-xl text-[15px] leading-8 text-zinc-500" data-motion-copy>
                We’ve replaced direct provider signup with a review-based onboarding flow. Share your business details here, and the admin team will review your request before creating dashboard access.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3" data-motion-group>
                <article class="rounded-[24px] border border-zinc-200 bg-white p-5 shadow-[0_18px_50px_-40px_rgba(15,23,42,0.25)]" data-motion-item data-motion-card>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">1. Apply</p>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Submit your business information, category, and optional supporting documents.</p>
                </article>
                <article class="rounded-[24px] border border-zinc-200 bg-white p-5 shadow-[0_18px_50px_-40px_rgba(15,23,42,0.25)]" data-motion-item data-motion-card>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">2. Review</p>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Admins review the request and can approve or reject it with notes.</p>
                </article>
                <article class="rounded-[24px] border border-zinc-200 bg-white p-5 shadow-[0_18px_50px_-40px_rgba(15,23,42,0.25)]" data-motion-item data-motion-card>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">3. Launch</p>
                    <p class="mt-3 text-sm leading-7 text-zinc-600">Once approved, we email your credentials so you can sign in and manage services.</p>
                </article>
            </div>
        </div>

        <div class="rounded-[32px] bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-8" data-motion-panel data-motion-card>
            <form action="{{ route('provider.requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="provider-request-business-name" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Business Name</label>
                    <input id="provider-request-business-name" type="text" name="business_name" value="{{ old('business_name') }}" required class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                </div>

                <div>
                    <label for="provider-request-owner-name" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Owner Name</label>
                    <input id="provider-request-owner-name" type="text" name="owner_name" value="{{ old('owner_name') }}" required class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="provider-request-email" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Email</label>
                        <input id="provider-request-email" type="email" name="email" value="{{ old('email') }}" required class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    </div>
                    <div>
                        <label for="provider-request-phone" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Phone</label>
                        <input id="provider-request-phone" type="text" name="phone" value="{{ old('phone') }}" required class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    </div>
                </div>

                <div>
                    <label for="provider-request-category" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service Category</label>
                    <select id="provider-request-category" name="service_category_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('service_category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="provider-request-business-details" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Business Details</label>
                    <textarea id="provider-request-business-details" name="business_details" rows="4" required class="mt-2 w-full rounded-[18px] border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">{{ old('business_details') }}</textarea>
                </div>

                <div>
                    <label for="provider-request-address" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Address</label>
                    <textarea id="provider-request-address" name="address" rows="3" required class="mt-2 w-full rounded-[18px] border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label for="provider-request-documents" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Documents (Optional)</label>
                    <input id="provider-request-documents" type="file" name="documents[]" multiple class="mt-2 block w-full rounded-[14px] border border-dashed border-zinc-300 bg-zinc-50 px-4 py-3 text-sm text-zinc-700 file:mr-3 file:rounded-full file:border-0 file:bg-zinc-950 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800">
                    <p class="mt-2 text-xs leading-6 text-zinc-500">Upload up to 5 files. Supported formats: PDF, JPG, PNG, DOC, DOCX.</p>
                </div>

                <button class="inline-flex h-12 w-full items-center justify-center rounded-[14px] bg-zinc-950 px-4 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                    Submit Provider Request
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
