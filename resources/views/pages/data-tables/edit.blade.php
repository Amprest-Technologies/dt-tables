@extends('dt-tables::pages.layouts.app')
@section('title', $page['name'])
@section('content')
    {{-- Header --}}
    @push('heading-badge')
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $modes[$dataTable->mode]['badge'] }}">
            <span class="h-1.5 w-1.5 rounded-full {{ $modes[$dataTable->mode]['dot'] }}"></span>
            {{ $modes[$dataTable->mode]['label'] }}
        </span>
    @endpush
    @push('heading-actions')
        @if ($page['guide'])
            <a href="{{ $page['guide']['url'] }}" class="btn-secondary">{{ $page['guide']['label'] }}</a>
        @endif
        <a href="{{ route('dt-tables.data-tables.index') }}" class="btn-secondary">← All tables</a>
    @endpush
    @include('dt-tables::pages.partials.heading', [
        'eyebrow' => 'Admin / Data tables',
        'title' => $page['name'],
    ])

    <div class="flex flex-col gap-6 lg:flex-row" data-section-page data-storage-key="dtt-section:{{ $dataTable->key }}">
        {{-- Section navigation --}}
        <nav class="lg:w-52 lg:shrink-0" aria-label="Sections">
            <ul class="flex gap-1 overflow-x-auto lg:sticky lg:top-20 lg:flex-col">
                @foreach($page['sections'] as $section)
                    <li>
                        <button type="button" data-section-link="{{ $section['id'] }}" aria-selected="{{ $section['id'] === $page['initial'] ? 'true' : 'false' }}"
                                class="block w-full whitespace-nowrap rounded-md border-l-2 border-transparent px-3 py-1.5 text-left text-sm text-gray-600 cursor-pointer transition hover:bg-gray-100 hover:text-secondary-dark aria-selected:border-primary aria-selected:bg-primary/10 aria-selected:font-semibold aria-selected:text-primary-darker {{ $section['id'] === 'danger' ? 'lg:mt-4' : '' }}">
                            {{ $section['title'] }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Sections --}}
        <div class="min-w-0 flex-1">
            @foreach($page['sections'] as $section)
                <section id="section-{{ $section['id'] }}" data-section="{{ $section['id'] }}" class="{{ $section['id'] === $page['initial'] ? '' : 'hidden' }}">
                    <div class="mb-5 border-b border-gray-200 pb-3">
                        <h2 class="text-2xl font-semibold text-secondary-dark">{{ $section['title'] }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $section['description'] }}</p>
                    </div>
                    @include('dt-tables::pages.data-tables.sections.'.$section['partial'], ['section' => $section])
                </section>
            @endforeach
        </div>
    </div>
@endsection
