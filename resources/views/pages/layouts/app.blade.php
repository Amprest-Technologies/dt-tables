@use('Nyumbani\DtTables\Docs\DtTablesDocs')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') — DtTables for Laravel</title>
    <x-data-table-assets mode="admin" />
</head>
<body class="min-h-screen bg-gray-50 text-gray-700 antialiased">
    {{--  Top bar  --}}
    <header class="sticky top-0 z-30 flex h-16 items-center gap-3 bg-secondary-dark px-4 text-white shadow-md lg:px-6">
        <a href="{{ route('dt-tables.home') }}" class="flex items-center gap-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-md bg-primary text-white">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/></svg>
            </span>
            <span class="text-sm font-bold uppercase tracking-[.2rem]">Nyumbani</span>
        </a>

        <nav class="ml-4 flex items-center gap-1 text-xs" aria-label="Admin">
            <a href="{{ route('dt-tables.home') }}" @class(['rounded px-2.5 py-1 transition', 'bg-white/15 font-semibold text-white' => request()->routeIs('dt-tables.home'), 'text-gray-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('dt-tables.home')])>Home</a>
            <a href="{{ route('dt-tables.data-tables.index') }}" @class(['rounded px-2.5 py-1 transition', 'bg-white/15 font-semibold text-white' => request()->routeIs('dt-tables.data-tables.*'), 'text-gray-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('dt-tables.data-tables.*')])>Tables</a>
        </nav>

        <div class="ml-auto flex items-center gap-4 text-xs">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 font-medium text-primary-lighter">
                <span class="h-1.5 w-1.5 rounded-full bg-primary"></span>
                {{ app()->environment() }}
            </span>
            @if (DtTablesDocs::url())
                <a href="{{ DtTablesDocs::url() }}" class="rounded border border-white/20 px-3 py-1.5 transition hover:bg-white/10">Docs</a>
            @endif
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @yield('content')
    </main>
    @if(session()->has('alert'))
        <script type="module">
            toastr[`{{ session('alert.type') }}`](`{{ session('alert.message') }}`);
        </script>
    @endif
    @stack('scripts')
</body>
</html>
