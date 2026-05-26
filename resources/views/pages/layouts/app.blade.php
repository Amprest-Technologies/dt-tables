<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') — DtTables</title>
    <x-data-table-assets mode="admin" />
</head>
<body class="bg-gray-100 text-gray-700 min-h-screen">
    <header class="bg-white border-b border-gray-200 shadow-xs">
        <div class="max-w-5xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-indigo-600 font-bold text-base">DtTables</span>
                <span class="text-gray-300">|</span>
                <span class="text-sm text-gray-400">Admin</span>
            </div>
            <a href="{{ route('dt-tables.data-tables.index') }}" class="text-sm text-indigo-600 hover:underline">
                Tables
            </a>
        </div>
    </header>
    <main class="max-w-5xl mx-auto px-6 py-8">
        @yield('content')
    </main>
    @if(session()->has('alert'))
        <script type="module">
            toastr[`{{ session('alert.type') }}`](`{{ session('alert.message') }}`);
        </script>
    @endif
</body>
</html>
