<form action="{{ $form['action'] }}" method="POST" class="rounded-xl border border-gray-200 bg-white shadow-xs">
    @csrf
    @method('PUT')
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-gray-800">{{ $form['title'] }}</h3>
        <button type="submit" class="btn">Save</button>
    </div>
    <div class="space-y-4 px-5 py-4">
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($form['fields'] as $field)
                @include('dt-tables::pages.data-tables.partials.field', ['field' => $field])
            @endforeach
        </div>
        @if($form['notice'])
            <p class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-500">{{ $form['notice'] }}</p>
        @endif
    </div>
</form>
