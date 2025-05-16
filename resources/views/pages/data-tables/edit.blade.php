@extends('laravel-dt::pages.layouts.app')
@section('title', prettify($dataTable->identifier))
@section('content')
    <section class="mb-3">
        <h1 class="font-semibold text-xl">
            Edit Configurations : {{ prettify($dataTable->identifier) }}
        </h1>
    </section>
    <section class="mb-5">
        <div class="mb-3 font-semibold">Column Configurations</div>
        <div>
            <form action="{{ route('laravel-dt.data-tables.data-table-columns.store', ['data_table' => $dataTable]) }}" method="POST" class="mb-1">
                @csrf
                <div class="element-group">
                    <input name="key" type="text" placeholder="List a new column">
                    <button class="btn">Add Column</button>
                </div>
            </form>
            <div>
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
                                    <input name="key" type="text" value="{{ $column->name }}" placeholder="Column Name" form="{{ $form = 'update-'.$column->getRouteKey().'-form' }}">
                                </td>
                                <td>
                                    <select name="search_type" form="{{ $form }}">
                                        @foreach (config('laravel-dt.columns.search_types') as $searchType)
                                            <option value="{{ $searchType }}" @selected($searchType == $column->search_type)>{{ prettify($searchType) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="data_type" form="{{ $form }}">
                                        @foreach (config('laravel-dt.columns.data_types') as $dataType)
                                            <option value="{{ $dataType }}" @selected($dataType == $column->data_type)>{{ prettify($dataType) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <div class="element-group justify-center">
                                        <form id="{{ $form }}" action="{{ route('laravel-dt.data-table-columns.update', ['data_table_column' => $column]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn">Update</button>
                                        </form>
                                        <form action="{{ route('laravel-dt.data-table-columns.destroy', ['data_table_column' => $column]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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