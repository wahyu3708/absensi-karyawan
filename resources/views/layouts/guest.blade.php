<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="Sistem Absensi Karyawan - Login">

    <link rel="manifest" href="/manifest.json">

    <title>@yield('title', 'Login - Absensi Karyawan')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-950 text-gray-100 min-h-screen font-sans antialiased flex items-center justify-center">
    @yield('content')
</body>

</html>
