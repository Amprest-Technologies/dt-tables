@extends('laravel-dt::pages.layouts.app')
@section('title', 'List of Data Tables')
@section('content')
    <div class="mb-3">
        <div class="mb-1 font-semibold">List a new table</div>
        <form action="{{ route('laravel-dt.data-tables.store') }}" method="POST">
            @csrf
            <div class="element-group">
                <input name="identifier" type="text" class="@error('identifier') border border-red-800 @enderror" placeholder="The table identifier eg. shoes-table" value="{{ old('identifier') }}">
                <button class="btn">Submit</button>
            </div>
            @error('identifier')
                <small class="text-red-900">{{ $message }}</small>
            @enderror
        </form>
    </div>
    <div class="mb-2">
        @if($dataTables->isNotEmpty())
            <div class="mb-1 font-semibold">List of all tables</div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-[5%] text-center">ID</th>
                        <th class="w-[80%]">Table Identifier</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataTables as $dataTable)
                        <tr>
                            <th class="text-center">{{ $loop->iteration }}</th>
                            <td>{{ $dataTable->identifier }}</td>
                            <td>
                                <div class="element-group justify-center">
                                    <a href="{{ route('laravel-dt.data-tables.edit', ['data_table' => $dataTable]) }}" class="btn">Edit</a>
                                    <button type="submit" form="{{ $form = 'delete-'.$dataTable->getRouteKey().'-form' }}" class="btn">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <form id="{{ $form }}" action="{{ route('laravel-dt.data-tables.destroy', ['data_table' => $dataTable]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="mb-1 border border-gray-200 rounded-lg p-4">
                No tables listed yet
            </div>
        @endif
    </div>
@endsection