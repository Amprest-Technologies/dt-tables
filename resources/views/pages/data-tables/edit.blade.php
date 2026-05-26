@extends('dt-tables::pages.layouts.app')
@section('title', prettify($dataTable->key))
@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-xs text-gray-400 mb-1">
                <a href="{{ route('dt-tables.data-tables.index') }}" class="hover:text-indigo-600">Tables</a>
                <span class="mx-1">/</span>
                <span>{{ prettify($dataTable->key) }}</span>
            </div>
            <h1 class="text-xl font-semibold text-gray-800">{{ prettify($dataTable->key) }}</h1>
        </div>
        <a href="{{ route('dt-tables.data-tables.index') }}" class="text-sm text-indigo-600 hover:underline">← Back</a>
    </div>

    {{-- Column Configurations --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-xs p-5 mb-1">
        <div class="text-sm font-semibold text-gray-700 mb-4">Column Configurations</div>

        <form action="{{ route('dt-tables.data-tables.columns.store', ['data_table' => $dataTable->id]) }}" method="POST" class="mb-4">
            @csrf
            @php $id = 'new-column' @endphp
            {!! bag($id) !!}
            <div class="element-group">
                <input name="key" type="text" class="@error('key', $id) border-red-500! @enderror" placeholder="Add a new column key e.g. tenant_name" value="{{ old('key') }}">
                <button class="btn">Add Column</button>
            </div>
            @error('key', $id)
                <small class="mt-1 block text-red-700">{{ $message }}</small>
            @enderror
        </form>

        @if($columns->isNotEmpty())
            <table class="table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-[5%] text-center">#</th>
                        <th class="w-[35%]">Column Key</th>
                        <th class="w-[20%]">Search Type</th>
                        <th class="w-[25%]">HTML Classes</th>
                        <th class="w-[15%] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($columns as $column)
                        @php $id = $column->id @endphp
                        <tr>
                            <td class="text-center text-gray-400">{{ $loop->iteration }}</td>
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
                                <input name="classes" type="text" value="{{ old('classes', $column->classes) }}" placeholder="e.g. text-center fw-bold" class="@error('classes', $id) border-red-500! @enderror" form="{{ $form }}">
                                @error('classes', $id)
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
            <div class="py-8 text-center text-sm text-gray-400 border border-gray-200 rounded-lg">
                No columns added yet.
            </div>
        @endif
    </div>

    {{-- Theme + Buttons --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-1 mb-1">
        <div class="bg-white border border-gray-200 rounded-xl shadow-xs p-5">
            <div class="text-sm font-semibold text-gray-700 mb-4">Theme</div>
            <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'theme']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label>Table Theme</label>
                    <select name="theme">
                        <option value="bootstrap" @selected($settings->theme === 'bootstrap')>Bootstrap</option>
                        <option value="tailwind" @selected($settings->theme === 'tailwind')>Tailwind</option>
                    </select>
                </div>
                <button class="btn w-full">Save Theme</button>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-xs p-5">
            <div class="text-sm font-semibold text-gray-700 mb-4">Export Buttons</div>
            <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'buttons']) }}" method="POST">
                @csrf
                @method('PUT')
                @foreach(config('dt-tables.settings.buttons') as $button)
                    <div class="mb-4">
                        <label>{{ prettify($button) }}</label>
                        <select name="buttons[{{ $button }}]">
                            <option value="1" @selected(in_array($button, $settings->buttons))>Active</option>
                            <option value="0" @selected(!in_array($button, $settings->buttons))>Inactive</option>
                        </select>
                    </div>
                @endforeach
                <button class="btn w-full">Save Buttons</button>
            </form>
        </div>
    </div>

    {{-- Loader --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-xs p-5">
        <div class="text-sm font-semibold text-gray-700 mb-4">Loader</div>
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'loader']) }}" method="POST">
            @csrf
            @method('PUT')
            @php $loader = $settings->loader ?? null; @endphp
            <div class="space-y-4">
                <div>
                    <label>Enabled</label>
                    <select name="loader[enabled]">
                        @php $enabled = old('loader.enabled', (bool) ($loader->enabled ?? 0)); @endphp
                        <option value="1" @selected($enabled)>Yes</option>
                        <option value="0" @selected(!$enabled)>No</option>
                    </select>
                </div>
                <div>
                    @php $message = old('loader.message', $loader->message ?? null); @endphp
                    <label>Message</label>
                    <input type="text" name="loader[message]" value="{{ $message }}" placeholder="Loading message...">
                </div>
                <div>
                    @php $image = old('loader.image', $loader->image ?? null); @endphp
                    <label>Image Path</label>
                    <input type="text" name="loader[image]" value="{{ $image }}" placeholder="img/loaders/app.svg">
                </div>
            </div>
            <button class="btn w-full">Save Loader</button>
        </form>
    </div>
@endsection
