@if($vite->isRunningHot())
    {{ $vite }}
@else
    @foreach ($assets() as $asset)
        @if ($asset->type === 'js')
            <script src="{{ $asset->path }}" defer></script>
        @elseif ($asset->type === 'css')
            <link rel="stylesheet" href="{{ $asset->path }}">
        @endif
    @endforeach
@endif