@if(app(\Illuminate\Foundation\Vite::class)->isRunningHot())
    @vite('vendor/amprest/laravel-dt/resources/js/app.js')
@else
    @foreach ($assets as $asset)
        @if ($asset->type === 'js')
            <script src="{{ $asset->path }}" defer ></script>
        @elseif ($asset->type === 'css')
            <link rel="stylesheet" href="{{ $asset->path }}">
        @endif
    @endforeach
@endif