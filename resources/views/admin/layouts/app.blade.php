<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-compact layout-menu-fixed" dir="ltr" data-skin="default"
    data-assets-path="/admin/" data-template="vertical-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title', settings('business_settings', 'company_name'))</title>
    <link rel="icon" type="image/x-icon" href="{{ imageShow(settings('business_settings', 'favicon')) }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/fonts/iconify-icons.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/node-waves/node-waves.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/pickr/pickr-themes.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/css/core.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/css/demo.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/bs-stepper/bs-stepper.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/sweetalert2/sweetalert2.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/fonts/flag-icons.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/css/pages/cards-advance.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/iziToast/iziToast.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet"
        href="{{ asset('admin') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/select2/select2.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/tagify/tagify.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/bootstrap-select/bootstrap-select.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/typeahead-js/typeahead.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/@form-validation/form-validation.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/flatpickr/flatpickr.css">
    <script src="{{ asset('admin') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('admin') }}/vendor/js/template-customizer.js"></script>
    <script src="{{ asset('admin') }}/js/config.js"></script>
    @livewireStyles
    @stack('styles')
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('admin.layouts.includes.sidebar')
            <div class="layout-page">
                @include('admin.layouts.includes.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    @include('admin.layouts.includes.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <script src="{{ asset('admin') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('admin') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@algolia/autocomplete-js.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/pickr/pickr.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('admin') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/bs-stepper/bs-stepper.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/iziToast/iziToast.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/tagify/tagify.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/moment/moment.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/bloodhound/bloodhound.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/cleave-zen/cleave-zen.js"></script>
    <script src="{{ asset('admin') }}/js/main.js"></script>
    @include('admin.layouts.includes.scripts')
    @livewireScripts
    @stack('scripts')
</body>

</html>
