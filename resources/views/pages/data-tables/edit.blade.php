@extends('laravel-dt::pages.layouts.app')
@section('title', prettify($dataTable->identifier))
@section('content')
    <section class="mb-3">
        <h1 class="font-semibold text-xl">
            Edit Configurations : {{ prettify($dataTable->identifier) }}
        </h1>
    </section>
    <section class="mb-3">
        <div class="mb-1">
            <div class="mb-1 font-semibold">Column Configurations</div>
            <form action="{{ route('laravel-dt.data-tables.store') }}" method="POST">
                @csrf
                <div class="element-group">
                    <input type="text" placeholder="List a new column" name="identifier">
                    <button class="btn">Add Column</button>
                </div>
            </form>
        </div>
        <div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-[5%] text-center">#</th>
                        <th class="w-[45%]">Column Title</th>
                        <th class="w-[15%]">Search Type</th>
                        <th class="w-[15%]">Data Type</th>
                        <th class="w-[20%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="w-[5%] text-center">#</td>
                        <td class="w-[45%]">Column Title</td>
                        <td class="w-[15%]">Search Type</td>
                        <td class="w-[15%]">Data Type</td>
                        <td class="w-[20%]">Actions</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection