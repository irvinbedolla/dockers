<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Bootstrap 5.3.3 -->
    <link href="../../../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


    <!-- Ionicons -->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="../../../public/assets/css/@fortawesome/fontawesome-free/css/all.css" rel="stylesheet" type="text/css">
    <link href="../../../public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="../../../public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="../../../public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    

        <!-- Agregados para los Select del Formulario Personas-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

        <style>
            .loader {
                position: fixed;
                left: 0px;
                top: 0px;
                width: 100%;
                height: 100%;
                z-index: 9999;
                background: url('../../../public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
                opacity: .8;
            }
        </style>
    @livewireStyles


    @yield('page_css')
        <!-- Template CSS -->
        <link rel="icon"       href="../../../public/assets/images/ccl-r.png" type="image/x-icon">
        <link rel="stylesheet" href="../../../public/assets/css/style.css">
        <link rel="stylesheet" href="../../../public/assets/css/components.css">
    @yield('page_css')

    @yield('page_css')
    <!-- Template CSS -->
    @yield('page_css')

</head>
<body>

<div id="app">
    <div class="main-wrapper main-wrapper-1">
        <div class="navbar-bg"></div>
        <nav class="navbar navbar-expand-lg main-navbar" style="background-color: #6A0F49">
            @include('layouts.header_editar1')

        </nav>
        <div class="main-sidebar main-sidebar-postion">
            @include('layouts.sidebar_editar1')
        </div>
        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
        <footer class="main-footer">
            @include('layouts.footer')
        </footer>
    </div>
</div>


</body>





<script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.1/js/dataTables.bootstrap4.js"></script>

<script src="../../../public/assets/js/jquery.min.js"></script>
<script src="../../../public/assets/js/popper.min.js"></script>
<script src="../../../public/assets/js/bootstrap.min.js"></script>
<script src="../../../public/assets/js/sweetalert.min.js"></script>
<script src="../../../public/assets/js/select2.min.js"></script>
<script src="../../../public/assets/js/jquery.nicescroll.js"></script>

<!-- Template JS File -->
<script src="../../../public/web/js/stisla.js"></script>
<script src="../../../public/web/js/scripts.js"></script>
<script src="../../../public/assets/js/profile.js"></script>
<script src="../../../public/assets/js/custom/custom.js"></script>
@yield('page_js')
@yield('scripts')
<script>
    let loggedInUser =@json(\Illuminate\Support\Facades\Auth::user());
    let loginUrl = '{{ route('login') }}';
    const userUrl = '{{url('users')}}';
    // Loading button plugin (removed from BS4)
    (function ($) {
        $.fn.button = function (action) {
            if (action === 'loading' && this.data('loading-text')) {
                this.data('original-text', this.html()).html(this.data('loading-text')).prop('disabled', true);
            }
            if (action === 'reset' && this.data('original-text')) {
                this.html(this.data('original-text')).prop('disabled', false);
            }
        };
    }(jQuery));
</script>
</html>
