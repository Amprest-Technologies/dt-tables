@extends('dt-tables::pages.layouts.app')
@section('title', $page['name'])
@section('content')
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <div>
            <nav class="text-xs text-gray-400 mb-1">
                <a href="{{ route('dt-tables.data-tables.index') }}" class="hover:text-primary-darker">Tables</a>
                <span class="mx-1">/</span>
                <span>{{ $page['name'] }}</span>
            </nav>
            <h1 class="flex flex-wrap items-center gap-2 text-xl font-semibold text-secondary">
                {{ $page['name'] }}
                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $modes[$dataTable->mode]['badge'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $modes[$dataTable->mode]['dot'] }}"></span>
                    {{ $modes[$dataTable->mode]['label'] }}
                </span>
            </h1>
        </div>
        <div class="flex items-center gap-1">
            <a href="{{ $page['guide']['url'] }}" class="btn-secondary">{{ $page['guide']['label'] }}</a>
            <a href="{{ route('dt-tables.data-tables.index') }}" class="btn-secondary">← All tables</a>
        </div>
    </div>

    <div class="mt-6 flex flex-col gap-1 lg:flex-row" data-section-page data-storage-key="dtt-section:{{ $dataTable->key }}">
        {{-- Section navigation --}}
        <nav class="lg:w-52 lg:shrink-0" aria-label="Sections">
            <ul class="flex gap-1 overflow-x-auto lg:sticky lg:top-20 lg:flex-col">
                @foreach($page['sections'] as $section)
                    <li>
                        <button type="button" data-section-link="{{ $section['id'] }}" aria-selected="{{ $section['id'] === $page['initial'] ? 'true' : 'false' }}"
                                class="w-full whitespace-nowrap rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-600 cursor-pointer hover:bg-gray-100 aria-selected:bg-primary-soft aria-selected:text-primary-darker {{ $section['id'] === 'danger' ? 'lg:mt-4' : '' }}">
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
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-secondary">{{ $section['title'] }}</h2>
                        <p class="text-sm text-gray-500">{{ $section['description'] }}</p>
                    </div>
                    @include('dt-tables::pages.data-tables.sections.'.$section['partial'], ['section' => $section])
                </section>
            @endforeach
        </div>
    </div>
@endsection
