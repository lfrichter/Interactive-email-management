<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Inbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @livewireStyles

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <main class="max-w-4xl mx-auto mt-10 p-4 bg-white rounded-xl shadow">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
