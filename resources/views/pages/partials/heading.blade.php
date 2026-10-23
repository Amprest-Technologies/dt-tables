{{--  The title of a page: an eyebrow, the heading, a lead and the accent bar. A page pushes a badge to heading-badge and its buttons to heading-actions before including it  --}}
<div class="mb-8">
    @isset($eyebrow)
        <p class="mb-2 text-xs font-bold uppercase tracking-widest text-primary-dark">{{ $eyebrow }}</p>
    @endisset
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="flex flex-wrap items-center gap-3 text-3xl font-bold tracking-tight text-secondary-dark">
                {{ $title }}
                @stack('heading-badge')
            </h1>
            @isset($lead)
                <p class="mt-2 text-lg leading-relaxed text-gray-500">{{ $lead }}</p>
            @endisset
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @stack('heading-actions')
        </div>
    </div>
    <div class="mt-4 h-1 w-16 rounded-full bg-linear-to-r from-primary to-primary-dark"></div>
</div>
