@extends('dt-tables::pages.layouts.app')
@section('title', prettify($dataTable->key))
@section('content')
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <nav class="text-xs text-gray-400 mb-1">
                <a href="{{ route('dt-tables.data-tables.index') }}" class="hover:text-indigo-600">Tables</a>
                <span class="mx-1">/</span>
                <span>{{ prettify($dataTable->key) }}</span>
            </nav>
            <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                {{ prettify($dataTable->key) }}
                <span class="font-mono text-xs bg-indigo-50 text-indigo-500 border border-indigo-100 px-2 py-0.5 rounded-full">{{ $dataTable->key }}</span>
            </h1>
        </div>
        <a href="{{ route('dt-tables.data-tables.index') }}" class="text-sm text-indigo-600 hover:underline">← Back</a>
    </div>

    {{-- Column Configurations --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-xs overflow-hidden mb-1 px-5 py-4">
        <div class="py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-gray-800">Columns</div>
                <div class="text-xs text-gray-400 mt-0.5">Keys and per-column search filters</div>
            </div>
            @if($columns->isNotEmpty())
                <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $columns->count() }} {{ Str::plural('column', $columns->count()) }}</span>
            @endif
        </div>

        <div class="py-3 border-b border-gray-100">
            <form action="{{ route('dt-tables.data-tables.columns.store', ['data_table' => $dataTable->id]) }}" method="POST">
                @csrf
                @php $id = 'new-column' @endphp
                {!! bag($id) !!}
                <div class="element-group">
                    <input name="key" type="text" class="@error('key', $id) border-red-500! @enderror" placeholder="New column key e.g. tenant_name" value="{{ old('key') }}">
                    <button class="btn shrink-0">Add Column</button>
                </div>
                @error('key', $id)
                    <small class="mt-1 block text-red-700">{{ $message }}</small>
                @enderror
            </form>
        </div>

        @if($columns->isNotEmpty())
            <table class="table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-[5%] text-center">#</th>
                        <th class="w-[50%]">Key</th>
                        <th class="w-[25%]">Search</th>
                        <th class="w-[20%] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($columns as $column)
                        @php $id = $column->id @endphp
                        <tr>
                            <td class="text-center text-xs text-gray-400">{{ $loop->iteration }}</td>
                            <td>
                                <input name="key" type="text" value="{{ old('key', prettify($column->key)) }}" placeholder="Column key" class="@error('key', $id) border-red-500! @enderror" form="{{ $form = 'update-'.$id.'-form' }}">
                                @error('key', $id)
                                    <small class="mt-0.5 block text-red-700">{{ $message }}</small>
                                @enderror
                            </td>
                            <td>
                                <select name="search_type" class="@error('search_type', $id) border-red-500! @enderror" form="{{ $form }}">
                                    @foreach (config('dt-tables.columns.search_types') as $searchType)
                                        <option value="{{ $searchType }}" @selected($searchType == old('search_type', $column->search_type))>{{ prettify($searchType) }}</option>
                                    @endforeach
                                </select>
                                @error('search_type', $id)
                                    <small class="mt-0.5 block text-red-700">{{ $message }}</small>
                                @enderror
                            </td>
                            <td>
                                <div class="element-group justify-center">
                                    <button form="{{ $form }}" type="submit" class="btn">Save</button>
                                    <button form="{{ $deleteForm = 'delete-'.$id.'-form' }}" type="submit" class="btn bg-red-600! hover:bg-red-700!">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <form id="{{ $form }}" action="{{ route('dt-tables.data-tables.columns.update', ['data_table' => $dataTable->id, 'data_table_column' => $id]) }}" method="POST">
                            @csrf
                            {!! bag($id) !!}
                            @method('PUT')
                        </form>
                        <form id="{{ $deleteForm }}" action="{{ route('dt-tables.data-tables.columns.destroy', ['data_table' => $dataTable->id, 'data_table_column' => $id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="py-12 text-center text-sm text-gray-400">
                No columns configured yet. Add one above.
            </div>
        @endif
    </div>

    {{-- Settings (all sections in one card) --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-xs overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <div class="text-sm font-semibold text-gray-800">Settings</div>
        </div>

        {{-- Name --}}
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'name']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Name</span>
                    <button class="btn shrink-0">Save</button>
                </div>
                <input type="text" name="key" value="{{ old('key', $dataTable->key) }}" placeholder="e.g. invoices-table">
                @error('key')
                    <small class="mt-1 block text-red-700">{{ $message }}</small>
                @enderror
            </div>
        </form>

        {{-- Theme --}}
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'theme']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Theme</span>
                    <button class="btn shrink-0">Save</button>
                </div>
                <select name="theme">
                    <option value="bootstrap" @selected($settings->theme === 'bootstrap')>Bootstrap</option>
                    <option value="tailwind" @selected($settings->theme === 'tailwind')>Tailwind</option>
                </select>
            </div>
        </form>

        {{-- Export Buttons --}}
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'buttons']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Export Buttons</span>
                    <button class="btn shrink-0">Save</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach(config('dt-tables.settings.buttons') as $button)
                        <div>
                            <label class="mb-2">{{ prettify($button) }}</label>
                            <div class="flex items-center gap-1.5">
                                <select name="buttons[{{ $button }}]">
                                    <option value="1" @selected(in_array($button, $settings->buttons))>Active</option>
                                    <option value="0" @selected(!in_array($button, $settings->buttons))>Inactive</option>
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>

        {{-- Behaviour --}}
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'behaviour']) }}" method="POST">
            @csrf
            @method('PUT')
            @php $behaviour = fluent((array) ($settings->behaviour ?? [])); @endphp
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Behaviour</span>
                    <button class="btn shrink-0">Save</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-2">Page Length</label>
                        @php $pageLength = old('page_length', $behaviour->page_length ?? 10); @endphp
                        <select name="page_length">
                            @foreach ([10, 25, 50, 100, -1] as $length)
                                <option value="{{ $length }}" @selected($pageLength == $length)>{{ $length === -1 ? 'All' : $length }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2">Ordering</label>
                        @php $ordering = old('ordering', (bool) ($behaviour->ordering ?? true)); @endphp
                        <select name="ordering">
                            <option value="1" @selected($ordering)>Enabled</option>
                            <option value="0" @selected(!$ordering)>Disabled</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2">Global Search</label>
                        @php $searching = old('searching', (bool) ($behaviour->searching ?? true)); @endphp
                        <select name="searching">
                            <option value="1" @selected($searching)>Enabled</option>
                            <option value="0" @selected(!$searching)>Disabled</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2">Pagination</label>
                        @php $paging = old('paging', (bool) ($behaviour->paging ?? true)); @endphp
                        <select name="paging">
                            <option value="1" @selected($paging)>Enabled</option>
                            <option value="0" @selected(!$paging)>Disabled</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2">Row Count Info</label>
                        @php $info = old('info', (bool) ($behaviour->info ?? true)); @endphp
                        <select name="info">
                            <option value="1" @selected($info)>Enabled</option>
                            <option value="0" @selected(!$info)>Disabled</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2">Horiz. Scroll</label>
                        @php $scrollX = old('scroll_x', (bool) ($behaviour->scroll_x ?? false)); @endphp
                        <select name="scroll_x">
                            <option value="1" @selected($scrollX)>Enabled</option>
                            <option value="0" @selected(!$scrollX)>Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        {{-- Loader --}}
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'loader']) }}" method="POST">
            @csrf
            @method('PUT')
            @php $loader = $settings->loader ?? null; @endphp
            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Loader</span>
                    <button class="btn shrink-0">Save</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-2">Enabled</label>
                        @php $enabled = old('loader.enabled', (bool) ($loader->enabled ?? 0)); @endphp
                        <select name="loader[enabled]">
                            <option value="1" @selected($enabled)>Yes</option>
                            <option value="0" @selected(!$enabled)>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2">Message</label>
                        @php $message = old('loader.message', $loader->message ?? null); @endphp
                        <input type="text" name="loader[message]" value="{{ $message }}" placeholder="Loading message...">
                    </div>
                    <div>
                        <label class="mb-2">Image Path</label>
                        @php $image = old('loader.image', $loader->image ?? null); @endphp
                        <input type="text" name="loader[image]" value="{{ $image }}" placeholder="img/loaders/app.svg">
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
