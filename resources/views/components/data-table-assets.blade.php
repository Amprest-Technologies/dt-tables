@if(is_file(package_path('public/hot')))
    {{ $vite }}
@else
    @foreach ($assets() as $asset)
        @if ($asset->type === 'js')
            <script type="module" src="{{ $asset->path }}" defer></script>
        @elseif ($asset->type === 'css')
            <link rel="stylesheet" href="{{ $asset->path }}">
        @endif
    @endforeach
@endif