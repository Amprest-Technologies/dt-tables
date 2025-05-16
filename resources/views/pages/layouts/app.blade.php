<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <x-data-table-assets />
</head>
<body>
    <div class="container mx-auto my-10">
        <div class="text-2xl mb-2 text-center font-semi-bold">Laravel Data Tables</div>
            @yield('content')
        </div>
    </div>
    @if(session()->has('alert'))
        <script type="module">
            toastr[`{{ session('alert.type') }}`](`{{ session('alert.message') }}`);
        </script>
    @endif
</body>
</html>