@extends('laravel-dt::pages.layouts.app')
@section('title', 'Laravel Data Tables')
@section('content')
    <div class="container mx-auto mt-5">
        <div class="text-2xl mb-2 text-center font-semi-bold">Laravel Data Tables</div>
        <div>
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
                        @foreach($tables as $table)
                            <tr>
                                <th class="text-center">{{ $loop->iteration }}</th>
                                <td>{{ $table->identifier }}</td>
                                <td>
                                    <div class="element-group justify-center">
                                        <button class="btn">Edit</button>
                                        <button class="btn">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection