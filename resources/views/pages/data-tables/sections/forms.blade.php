<div class="space-y-4">
    @foreach($section['forms'] as $form)
        @include('dt-tables::pages.data-tables.partials.form', ['form' => $form])
    @endforeach
</div>
