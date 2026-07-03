<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title') | SICCL</title>

    <!-- General CSS Files -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>

    <!-- Template CSS -->
    <link href="public/assets/css/style.css" rel="stylesheet">
    <link href="public/assets/css/components.css" rel="stylesheet">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
            opacity: .8;
        }
    </style>
        
</head>

<body>
<div id="app">
    <section class="section">
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="login-brand">
                        <img src="public/assets/images/ccl-r.png" alt="logo" width="100"
                             class="shadow-light">
                    </div>
                    @yield('content')
                    <div class="simple-footer">
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- General JS Scripts -->
<script src="public/assets/js/jquery.min.js"></script>
<script src="public/assets/js/popper.min.js"></script>
<script src="public/assets/js/bootstrap.min.js"></script>
<script src="public/assets/js/jquery.nicescroll.js"></script>

<!-- JS Libraies -->
<script type="text/javascript">
    const myForm = document.getElementById('login');
    login.addEventListener('submit', (event) => {
        $('#login_div').show();
    });
</script>
<!-- Template JS File -->
<script src="public/assets/js/stisla.js"></script>
<script src="public/assets/js/scripts.js"></script>
<!-- Page Specific JS File -->
</body>
</html>
