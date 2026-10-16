<div class="space-y-1">
    @foreach($section['forms'] as $form)
        @include('dt-tables::pages.data-tables.partials.form', ['form' => $form])
    @endforeach

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50/50 px-5 py-4">
        <div>
            <h3 class="text-sm font-semibold text-red-800">Delete this table</h3>
            <p class="text-xs text-red-700/80">Removes the table's file. Views that still render it will stop working.</p>
        </div>
        <form action="{{ $section['deleteAction'] }}" method="POST" onsubmit="return confirm(@js($section['confirm']))">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn bg-red-600! hover:bg-red-700!">Delete table</button>
        </form>
    </div>
</div>
