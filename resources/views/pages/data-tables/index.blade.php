@extends('dt-tables::pages.layouts.app')
@section('title', 'Data Tables')
@section('content')
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-secondary">Data Tables</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $tables->count() }} {{ Str::plural('table', $tables->count()) }} registered in <code class="text-xs">resources/data-tables</code>
            </p>
        </div>
        <button type="button" class="btn inline-flex items-center gap-1.5 py-2!" data-open-dialog="new-table">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            New table
        </button>
    </div>

    @if($tables->isNotEmpty())
        {{-- Search and mode filter --}}
        <div class="flex flex-wrap items-center gap-3 mb-2">
            <div class="relative w-full sm:w-72">
                <svg class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" id="table-search" class="pl-9! bg-white" placeholder="Search tables" autocomplete="off">
            </div>
            <div class="flex flex-wrap gap-1.5" role="tablist" id="mode-filter">
                <button type="button" data-mode-filter="" class="rounded-full bg-white px-3 py-1 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-200 cursor-pointer hover:bg-gray-100 aria-selected:bg-primary-darker aria-selected:text-white aria-selected:ring-primary-darker aria-selected:hover:bg-primary-darker" aria-selected="true">
                    All <span class="ml-0.5 text-xs opacity-70">{{ $tables->count() }}</span>
                </button>
                @foreach($modes as $mode => $meta)
                    @if(($counts[$mode] ?? 0) > 0)
                        <button type="button" data-mode-filter="{{ $mode }}" class="rounded-full bg-white px-3 py-1 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-200 cursor-pointer hover:bg-gray-100 aria-selected:bg-primary-darker aria-selected:text-white aria-selected:ring-primary-darker aria-selected:hover:bg-primary-darker" aria-selected="false">
                            {{ $meta['label'] }} <span class="ml-0.5 text-xs opacity-70">{{ $counts[$mode] }}</span>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Table cards --}}
        <div id="table-grid" class="grid gap-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tables as $table)
                <div class="table-card flex flex-col rounded-xl border border-gray-200 bg-white shadow-xs transition hover:border-gray-300 hover:shadow-sm"
                     data-key="{{ strtolower($table['key']) }}" data-mode="{{ $table['mode'] }}">
                    <div class="flex-1 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $modes[$table['mode']]['badge'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $modes[$table['mode']]['dot'] }}"></span>
                                {{ $modes[$table['mode']]['label'] }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $table['theme'] }}</span>
                        </div>
                        <a href="{{ route('dt-tables.data-tables.edit', ['data_table' => $table['key']]) }}" class="mt-3 block">
                            <div class="font-semibold text-secondary hover:text-primary-darker truncate">{{ $table['name'] }}</div>
                            <div class="font-mono text-xs text-gray-400 truncate">{{ $table['key'] }}</div>
                        </a>
                        <dl class="mt-4 grid grid-cols-3 gap-2 text-xs">
                            <div>
                                <dt class="text-gray-400">{{ $table['filtersLabel'] }}</dt>
                                <dd class="mt-0.5 font-medium text-gray-700">{{ $table['filters'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Page size</dt>
                                <dd class="mt-0.5 font-medium text-gray-700">{{ $table['pageLength'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Buttons</dt>
                                <dd class="mt-0.5 font-medium text-gray-700">{{ $table['buttons'] }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-5 py-3">
                        <a href="{{ route('dt-tables.data-tables.edit', ['data_table' => $table['key']]) }}" class="btn-secondary">Edit</a>
                        <form action="{{ route('dt-tables.data-tables.destroy', ['data_table' => $table['key']]) }}" method="POST"
                              onsubmit="return confirm('Delete table ' + @js($table['key']) + '? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-gray-400 hover:text-red-600 cursor-pointer">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Shown when the search and filter leave nothing --}}
        <div id="no-results" class="hidden rounded-xl border border-dashed border-gray-300 bg-white py-12 text-center text-sm text-gray-400">
            No tables match your search.
        </div>
    @else
        {{-- Empty state --}}
        <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-lg font-semibold text-secondary">Register your first table</h2>
                <p class="mt-1 text-sm text-gray-500">Pick how the table gets its rows. You can change this later.</p>
            </div>
            <div class="mx-auto mt-8 grid max-w-4xl gap-1 sm:grid-cols-3">
                @foreach($modes as $meta)
                    <div class="rounded-lg border border-gray-200 p-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $meta['badge'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                            {{ $meta['label'] }}
                        </span>
                        <p class="mt-3 text-sm text-gray-600">{{ $meta['summary'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <button type="button" class="btn py-2!" data-open-dialog="new-table">New table</button>
            </div>
        </div>
    @endif

    {{-- New table dialog --}}
    <dialog id="new-table" @if($errors->has('key') || $errors->has('mode')) data-open @endif class="m-auto w-full max-w-xl rounded-xl p-0 shadow-xl backdrop:bg-secondary-darker/50">
        <form action="{{ route('dt-tables.data-tables.store') }}" method="POST">
            @csrf
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-secondary">New table</h2>
                <p class="text-sm text-gray-500">Name the table and choose how it gets its rows.</p>
            </div>
            <div class="space-y-5 px-6 py-5">
                <div>
                    <label for="new-table-key">Table key</label>
                    <input id="new-table-key" name="key" type="text" class="@error('key') border-red-500! @enderror" placeholder="e.g. invoices-table" value="{{ old('key') }}" autocomplete="off">
                    @error('key')
                        <small class="mt-1 block text-red-700">{{ $message }}</small>
                    @enderror
                </div>
                <fieldset>
                    <legend class="mb-1 text-sm font-medium text-gray-600">Mode</legend>
                    <div class="flex flex-col gap-2">
                        @foreach($modes as $mode => $meta)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 mb-0! hover:bg-gray-50 has-checked:border-primary-dark has-checked:bg-primary-soft/60 has-checked:ring-1 has-checked:ring-primary-dark">
                                <input type="radio" name="mode" value="{{ $mode }}" class="mt-0.5 shrink-0 accent-primary-dark" @checked(old('mode') === $mode)>
                                <span>
                                    <span class="block text-sm font-medium text-secondary">{{ $meta['label'] }}</span>
                                    <span class="mt-0.5 block text-xs font-normal text-gray-500">{{ $meta['summary'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('mode')
                        <small class="mt-1 block text-red-700">{{ $message }}</small>
                    @enderror
                </fieldset>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                <button type="button" class="btn-secondary" data-close-dialog>Cancel</button>
                <button type="submit" class="btn">Create table</button>
            </div>
        </form>
    </dialog>
@endsection
