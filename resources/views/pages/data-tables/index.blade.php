@extends('dt-tables::pages.layouts.app')
@section('title', 'Data Tables')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Data Tables</h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-xs p-5 mb-1">
        <div class="text-sm font-semibold text-gray-700 mb-3">Register a Table</div>
        <form action="{{ route('dt-tables.data-tables.store') }}" method="POST">
            @csrf
            <div class="element-group">
                <input name="key" type="text" class="@error('key') border-red-500! @enderror" placeholder="e.g. invoices-table" value="{{ old('key') }}">
                <button class="btn">Add Table</button>
            </div>
            @error('key')
                <small class="mt-1 block text-red-700">{{ $message }}</small>
            @enderror
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-xs overflow-hidden">
        @if($dataTables->isNotEmpty())
            <table class="table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-[5%] text-center">#</th>
                        <th>Table Key</th>
                        <th class="w-[15%] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataTables as $dataTable)
                        <tr>
                            <td class="text-center text-gray-400">{{ $loop->iteration }}</td>
                            <td class="font-mono text-sm">{{ $dataTable->key }}</td>
                            <td>
                                <div class="element-group justify-center">
                                    <a href="{{ route('dt-tables.data-tables.edit', ['data_table' => $dataTable->id]) }}" class="btn">Edit</a>
                                    <button type="submit" form="{{ $form = 'delete-'.$dataTable->id.'-form' }}" class="btn bg-red-600! hover:bg-red-700!">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <form id="{{ $form }}" action="{{ route('dt-tables.data-tables.destroy', ['data_table' => $dataTable->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="py-12 text-center text-sm text-gray-400">
                No tables registered yet. Add one above.
            </div>
        @endif
    </div>
@endsection
