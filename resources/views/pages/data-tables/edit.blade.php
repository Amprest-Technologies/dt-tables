@extends('dt-tables::pages.layouts.app')
@section('title', prettify($dataTable->key))
@section('content')
    <section class="mb-3">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl">
                Edit Configurations : {{ prettify($dataTable->key) }}
            </h1>
            <div>
                <a href="{{ route('dt-tables.data-tables.index') }}" class="underline">Back Home</a>
            </div>
        </div>
    </section>
    <section class="p-4 mb-3 bg-gray-50 rounded-sm drop-shadow-sm">
        <div class="mb-3 font-semibold">Column Configurations</div>
        <div>
            <form action="{{ route('dt-tables.data-tables.columns.store', ['data_table' => $dataTable->id]) }}" method="POST" class="mb-1">
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
                            <th class="w-[15%]">Html Classes</th>
                            <th class="w-[20%] text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($columns as $column)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    @php $id = $column->id @endphp
                                    <input name="key" type="text" value="{{ old('key', prettify($column->key)) }}" placeholder="Column Name" class="@error('key', $id) border border-red-800 @enderror" form="{{ $form = 'update-'.$id.'-form' }}">
                                    @error('key', $id)
                                        <small class="text-red-900">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <select name="search_type" class="@error('search_type', $id) border border-red-800 @enderror" form="{{ $form }}">
                                        @foreach (config('dt-tables.columns.search_types') as $searchType)
                                            <option value="{{ $searchType }}" @selected($searchType == old('search_type', $column->search_type))>{{ prettify($searchType) }}</option>
                                        @endforeach
                                    </select>
                                    @error('search_type', $id)
                                        <small class="text-red-900">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <input name="classes" type="text" value="{{ old('classes', $column->classes) }}" placeholder="Valid html class syntax..." class="@error('classes', $id) border border-red-800 @enderror" form="{{ $form = 'update-'.$id.'-form' }}">
                                    @error('classes', $id)
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
                            <form id="{{ $form }}" action="{{ route('dt-tables.data-tables.columns.update', ['data_table' => $dataTable->id, 'data_table_column' => $id])  }}" method="POST">
                                @csrf
                                {!! bag($id) !!}
                                @method('PUT')
                            </form>
                            <form id="{{ $deleteForm }}" action="{{ route('dt-tables.data-tables.columns.destroy', ['data_table' => $dataTable->id, 'data_table_column' => $id])  }}" method="POST">
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
    <section class="p-4 mb-3 bg-gray-50 rounded-sm drop-shadow-sm">
        <div class="mb-3 font-semibold">Theme Configurations</div>
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'theme']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="mb-1.5 font-bold">Table Theme</label>
                <select name="theme">
                    <option value="bootstrap" @selected($settings->theme === 'bootstrap')>Bootstrap</option>
                    <option value="tailwind" @selected($settings->theme === 'tailwind')>Tailwind</option>
                </select>
            </div>
            <button class="btn w-full">Submit</button>
        </form>
    </section>
    <section class="p-4 mb-3 bg-gray-50 rounded-sm drop-shadow-sm">
        <div class="mb-3 font-semibold">Button Configurations</div>
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'buttons']) }}" method="POST">
            @csrf
            @method('PUT')
            @foreach(config('dt-tables.settings.buttons') as $button)
                <div class="mb-3">
                    <label class="mb-1.5 font-bold">{{ prettify($button) }}</label>
                    <select name="buttons[{{ $button }}]">
                        <option value="1" @selected(in_array($button, $settings->buttons))>Active</option>
                        <option value="0" @selected(!in_array($button, $settings->buttons))>Inactive</option>
                    </select>
                </div>
            @endforeach
            <button class="btn w-full">Submit</button>
        </form>
    </section>
    <section class="p-4 mb-3 bg-gray-50 rounded-sm drop-shadow-sm">
        <div class="mb-3 font-semibold">Loader Configurations</div>
        <form action="{{ route('dt-tables.data-tables.update', ['data_table' => $dataTable->id, 'type' => 'loader']) }}" method="POST">
            @csrf
            @method('PUT')
            @php $loader = $settings->loader ?? null; @endphp
            <div class="mb-3">
                <label class="mb-1.5 font-bold">Loader Enabled</label>
                <select name="loader[enabled]">
                    @php $enabled = old('loader.enabled',(bool) ($loader->enabled ?? 0)); @endphp
                    <option value="1" @selected($enabled)>Yes</option>
                    <option value="0" @selected(!$enabled)>No</option>
                </select>
            </div>
            <div class="mb-3">
                @php $message = old('loader.message', $loader->message ?? null); @endphp
                <label class="mb-1.5 font-bold">Loader Message</label>
                <input type="text" name="loader[message]" value="{{ $message }}" placeholder="Message to display when loading...">  
            </div>
            <div class="mb-3">
                @php $image = old('loader.image', $loader->image ?? null); @endphp
                <label class="mb-1.5 font-bold">Loader Image</label>
                <input type="text" name="loader[image]" value="{{ $image }}" placeholder="Image to display when loading...">  
            </div>
            <button class="btn w-full">Submit</button>
        </form>
    </section>
@endsection