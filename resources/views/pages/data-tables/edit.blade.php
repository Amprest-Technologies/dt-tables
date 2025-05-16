@extends('laravel-dt::pages.layouts.app')
@section('title', prettify($dataTable->identifier))
@section('content')
    <section class="mb-3">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl">
                Edit Configurations : {{ prettify($dataTable->identifier) }}
            </h1>
            <div>
                <a href="{{ route('laravel-dt.data-tables.index') }}" class="underline">Back Home</a>
            </div>
        </div>
    </section>
    <section class="mb-5">
        <div class="mb-3 font-semibold">Column Configurations</div>
        <div>
            <form action="{{ route('laravel-dt.data-tables.data-table-columns.store', ['data_table' => $dataTable]) }}" method="POST" class="mb-1">
                @csrf
                @php $id = 'new-column' @endphp
                {!! bag($id) !!}
                <div class="element-group">
                    <input name="key" type="text" class="@error('key', $id) border border-red-800 @enderror" placeholder="List a new column" value="{{ old('key') }}">
                    <button class="btn">Add Column</button>
                </div>
                @error('key', $id)
                    <small class="text-red-900">{{ $message }}</small>
                @enderror
            </form>
            @if($columns->isNotEmpty())
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-[5%] text-center">#</th>
                            <th class="w-[45%]">Column Name</th>
                            <th class="w-[15%]">Search Type</th>
                            <th class="w-[15%]">Data Type</th>
                            <th class="w-[20%] text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($columns as $column)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    @php $id = $column->getRouteKey() @endphp
                                    <input name="key" type="text" value="{{ old('key', $column->name) }}" placeholder="Column Name" class="@error('key', $id) border border-red-800 @enderror" form="{{ $form = 'update-'.$id.'-form' }}">
                                    @error('key', $id)
                                        <small class="text-red-900">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <select name="search_type" class="@error('search_type', $id) border border-red-800 @enderror" form="{{ $form }}">
                                        @foreach (config('laravel-dt.columns.search_types') as $searchType)
                                            <option value="{{ $searchType }}" @selected($searchType == old('search_type', $column))>{{ prettify($searchType) }}</option>
                                        @endforeach
                                    </select>
                                    @error('search_type', $id)
                                        <small class="text-red-900">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <select name="data_type" class="@error('data_type', $id) border border-red-800 @enderror" form="{{ $form }}">
                                        @foreach (config('laravel-dt.columns.data_types') as $dataType)
                                            <option value="{{ $dataType }}" @selected($dataType == old('data_type', $column))>{{ prettify($dataType) }}</option>
                                        @endforeach
                                    </select>
                                    @error('data_type', $id)
                                        <small class="text-red-900">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td class="text-center">
                                    <div class="element-group justify-center">
                                        <button form="{{ $form }}" type="submit" class="btn">Update</button>
                                        <button form="{{ $deleteForm = 'delete-'.$id.'-form' }}" type="submit" class="btn">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <form id="{{ $form }}" action="{{ route('laravel-dt.data-table-columns.update', ['data_table_column' => $column]) }}" method="POST">
                                @csrf
                                {!! bag($id) !!}
                                @method('PUT')
                            </form>
                            <form id="{{ $deleteForm }}" action="{{ route('laravel-dt.data-table-columns.destroy', ['data_table_column' => $column]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="mb-1 border border-gray-200 rounded-lg p-4">
                    No columns listed yet
                </div>
            @endif
        </div>
    </section>
    <section class="mb-3">
        <div class="mb-3 font-semibold">Button Configurations</div>
        <form action="{{ route('laravel-dt.data-tables.update', ['data_table' => $dataTable, 'type' => 'buttons']) }}" method="POST">
            @csrf
            @method('PUT')
            @foreach(config('laravel-dt.defaults.settings.buttons') as $button)
                <div class="mb-3">
                    <label class="mb-1">{{ prettify($button) }}</label>
                    <select name="buttons[{{ $button }}]">
                        <option value="1" @selected(in_array($button, $dataTable->settings['buttons']))>Active</option>
                        <option value="0" @selected(!in_array($button, $dataTable->settings['buttons']))>Inactive</option>
                    </select>
                </div>
            @endforeach
            <button class="btn w-full">Submit</button>
        </form>
    </section>
@endsection