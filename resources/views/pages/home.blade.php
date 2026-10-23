@extends('dt-tables::pages.layouts.app')
@section('title', 'Home')
@section('content')
    @include('dt-tables::pages.partials.heading', [
        'eyebrow' => 'Admin',
        'title' => 'DtTables',
        'lead' => 'Interactive tables for Laravel, from a small PHP class and a Blade component.',
    ])

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('dt-tables.data-tables.index') }}" class="group flex flex-col rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
            <span class="flex h-10 w-10 items-center justify-center rounded-sm bg-primary-soft text-primary-darker">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/></svg>
            </span>
            <h2 class="mt-4 text-lg font-semibold text-secondary">Tables</h2>
            <p class="mt-1 flex-1 text-sm text-gray-500">Register tables and change their settings, columns and filters.</p>
            <span class="mt-4 text-sm font-medium text-primary-darker group-hover:text-primary-dark">
                {{ $tableCount }} {{ Str::plural('table', $tableCount) }} registered →
            </span>
        </a>

        @if ($docs)
        <a href="{{ $docs->url() }}" class="group flex flex-col rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
            <span class="flex h-10 w-10 items-center justify-center rounded-sm bg-secondary-soft text-secondary-light">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 19V5"/></svg>
            </span>
            <h2 class="mt-4 text-lg font-semibold text-secondary">Documentation</h2>
            <p class="mt-1 flex-1 text-sm text-gray-500">Guides for inline, server-side and API tables, with code to copy.</p>
            <span class="mt-4 text-sm font-medium text-primary-darker group-hover:text-primary-dark">
                {{ $docs->pages()->count() }} pages →
            </span>
        </a>
        @endif
    </div>

    @if ($docs)
    <div class="mt-10">
        <h2 class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Start here</h2>
        <ul class="mt-2 grid gap-3 text-sm sm:grid-cols-3">
            <li><a href="{{ $docs->url('quick-start') }}" class="block rounded-md bg-white px-4 py-3 font-medium text-secondary-dark shadow-sm ring-1 ring-inset ring-gray-200 transition hover:ring-primary hover:text-primary-dark">Quick Start</a></li>
            <li><a href="{{ $docs->url('introduction') }}" class="block rounded-md bg-white px-4 py-3 font-medium text-secondary-dark shadow-sm ring-1 ring-inset ring-gray-200 transition hover:ring-primary hover:text-primary-dark">Choosing a mode</a></li>
            <li><a href="{{ $docs->url('server-side-overview') }}" class="block rounded-md bg-white px-4 py-3 font-medium text-secondary-dark shadow-sm ring-1 ring-inset ring-gray-200 transition hover:ring-primary hover:text-primary-dark">Large tables</a></li>
        </ul>
    </div>
    @endif
@endsection
