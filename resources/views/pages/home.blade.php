@extends('dt-tables::pages.layouts.app')
@section('title', 'Home')
@section('content')
    <div class="mx-auto max-w-3xl py-8 text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-md bg-primary text-secondary-darker">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/></svg>
        </span>
        <h1 class="mt-5 text-3xl font-bold tracking-tight text-secondary">DtTables</h1>
        <p class="mt-2 text-lg text-gray-500">Interactive tables for Laravel, from a small PHP class and a Blade component.</p>
    </div>

    <div class="mx-auto grid max-w-3xl gap-1 sm:grid-cols-2">
        <a href="{{ route('dt-tables.data-tables.index') }}" class="group flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-xs transition hover:border-primary-dark hover:shadow-sm">
            <span class="flex h-10 w-10 items-center justify-center rounded-sm bg-primary-soft text-primary-darker">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/></svg>
            </span>
            <h2 class="mt-4 text-lg font-semibold text-secondary">Tables</h2>
            <p class="mt-1 flex-1 text-sm text-gray-500">Register tables and change their settings, columns and filters.</p>
            <span class="mt-4 text-sm font-medium text-primary-darker group-hover:text-primary-dark">
                {{ $tableCount }} {{ Str::plural('table', $tableCount) }} registered →
            </span>
        </a>

        <a href="{{ route('dt-tables.docs.index') }}" class="group flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-xs transition hover:border-primary-dark hover:shadow-sm">
            <span class="flex h-10 w-10 items-center justify-center rounded-sm bg-secondary-soft text-secondary-light">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 19V5"/></svg>
            </span>
            <h2 class="mt-4 text-lg font-semibold text-secondary">Documentation</h2>
            <p class="mt-1 flex-1 text-sm text-gray-500">Guides for inline, server-side and API tables, with code to copy.</p>
            <span class="mt-4 text-sm font-medium text-primary-darker group-hover:text-primary-dark">
                {{ $pageCount }} pages →
            </span>
        </a>
    </div>

    <div class="mx-auto mt-8 max-w-3xl">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Start here</h2>
        <ul class="mt-2 grid gap-1 text-sm sm:grid-cols-3">
            <li><a href="{{ route('dt-tables.docs.show', ['page' => 'quick-start']) }}" class="block rounded-sm bg-white px-3 py-2 font-medium text-secondary ring-1 ring-inset ring-gray-200 hover:ring-primary-dark">Quick Start</a></li>
            <li><a href="{{ route('dt-tables.docs.show', ['page' => 'introduction']) }}" class="block rounded-sm bg-white px-3 py-2 font-medium text-secondary ring-1 ring-inset ring-gray-200 hover:ring-primary-dark">Choosing a mode</a></li>
            <li><a href="{{ route('dt-tables.docs.show', ['page' => 'server-side-overview']) }}" class="block rounded-sm bg-white px-3 py-2 font-medium text-secondary ring-1 ring-inset ring-gray-200 hover:ring-primary-dark">Large tables</a></li>
        </ul>
    </div>
@endsection
