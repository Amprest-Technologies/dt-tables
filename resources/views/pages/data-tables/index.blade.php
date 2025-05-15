@extends('laravel-dt::pages.layouts.app')
@section('title', 'List of Data Tables')
@section('content')
    <div class="mb-3">
        <div class="mb-1 font-semibold">List a new table</div>
        <form action="{{ route('laravel-dt.data-tables.store') }}" method="POST">
            @csrf
            <div class="element-group">
                <input type="text" placeholder="The Table Identifier" name="identifier">
                <button class="btn">List Table</button>
            </div>
        </form>
    </div>
    <div class="mb-2">
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
                                <form action="{{ route('laravel-dt.data-tables.destroy', ['data_table' => $dataTable]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection