<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <form action="{{ $section['storeAction'] }}" method="POST" class="border-b border-gray-100 px-5 py-4">
        @csrf
        <input type="hidden" name="_bag" value="new-column">
        <div class="element-group">
            <input name="key" type="text" class="@error('key', 'new-column') border-red-500! @enderror" placeholder="New column key e.g. tenant_name" value="{{ $section['newKey'] }}" autocomplete="off">
            <button class="btn shrink-0">Add Column</button>
        </div>
        @error('key', 'new-column')
            <small class="mt-1 block text-red-700">{{ $message }}</small>
        @enderror
    </form>

    @if($section['columns'])
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
                @foreach($section['columns'] as $column)
                    <tr>
                        <td class="text-center text-xs text-gray-400">{{ $loop->iteration }}</td>
                        <td>
                            <input name="key" type="text" value="{{ $column['key'] }}" placeholder="Column key" class="@error('key', $column['id']) border-red-500! @enderror" form="{{ $column['updateForm'] }}">
                            @error('key', $column['id'])
                                <small class="mt-0.5 block text-red-700">{{ $message }}</small>
                            @enderror
                        </td>
                        <td>
                            <select name="search_type" class="@error('search_type', $column['id']) border-red-500! @enderror" form="{{ $column['updateForm'] }}">
                                @foreach($column['searchTypes'] as $value => $label)
                                    <option value="{{ $value }}" @selected($value == $column['searchType'])>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('search_type', $column['id'])
                                <small class="mt-0.5 block text-red-700">{{ $message }}</small>
                            @enderror
                        </td>
                        <td>
                            <div class="element-group justify-center">
                                <button form="{{ $column['updateForm'] }}" type="submit" class="btn">Save</button>
                                <button form="{{ $column['deleteForm'] }}" type="submit" class="btn bg-red-600! hover:bg-red-700!" onclick="return confirm(@js($column['confirm']))">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <form id="{{ $column['updateForm'] }}" action="{{ $column['updateAction'] }}" method="POST">
                        @csrf
                        <input type="hidden" name="_bag" value="{{ $column['id'] }}">
                        @method('PUT')
                    </form>
                    <form id="{{ $column['deleteForm'] }}" action="{{ $column['deleteAction'] }}" method="POST">
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
