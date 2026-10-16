<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') — DtTables for Laravel</title>
    <x-data-table-assets mode="admin" />
</head>
<body class="bg-gray-100 text-gray-700 min-h-screen antialiased">
    <header class="sticky top-0 z-30 bg-secondary border-b border-secondary-dark shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dt-tables.home') }}" class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-sm bg-primary text-secondary-darker">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/></svg>
                    </span>
                    <span class="font-semibold tracking-wide text-white">DtTables</span>
                </a>
                <nav class="flex items-center gap-1 text-sm font-medium">
                    <a href="{{ route('dt-tables.data-tables.index') }}" @class(['rounded-sm px-3 py-1.5 text-gray-300 hover:text-white', 'bg-secondary-dark text-white' => request()->routeIs('dt-tables.data-tables.*')])>Tables</a>
                    <a href="{{ route('dt-tables.docs.index') }}" @class(['rounded-sm px-3 py-1.5 text-gray-300 hover:text-white', 'bg-secondary-dark text-white' => request()->routeIs('dt-tables.docs.*')])>Docs</a>
                </nav>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary-dark px-2.5 py-1 text-xs font-medium text-primary-lighter ring-1 ring-inset ring-primary/30">
                <span class="h-1.5 w-1.5 rounded-full bg-primary"></span>
                {{ app()->environment() }}
            </span>
        </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
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
