<!doctype html>
<html lang="en" data-theme="light">

@include('web.layouts.includes.head')

<body>
    @include('web.layouts.includes.navbar')
    @yield('content')
    </main>
    @include('web.layouts.includes.footer')
    @include('web.layouts.includes.scripts')
</body>

</html>
