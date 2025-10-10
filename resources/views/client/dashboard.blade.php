<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Default Title')</title>
</head>
<body>
    @include('partials.client.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.client.footer')
</body>
</html>