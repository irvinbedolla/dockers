<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Si concilio</title>
    <!-- Bootstrap 4.1.1 -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- Ionicons -->
    <!--<link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">-->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    @yield('page_css')
    <link rel="stylesheet" href="public/assets/css/components.css">
    @yield('page_css')
</head>
    <div class="container">
        <h1>Calendar</h1>
        <div id="calendar">
            calendario
        </div>
    </div>
    
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#evento">
      Launch
    </button>
    
    <!-- Modal -->
    <div class="modal fade" id="evento" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>
                <div class="modal-body">
                    Body
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

<script src="public/assets/js/calendar.js"></script>
<script src="public/assets/js/jquery.min.js"></script>
<script src="public/assets/js/popper.min.js"></script>
<script src="public/assets/js/bootstrap.min.js"></script>
<script src="public/assets/js/sweetalert.min.js"></script>
<script src="public/assets/js/select2.min.js"></script>
<script src="public/assets/js/jquery.nicescroll.js"></script>

<!-- Template JS File -->
<script src="public/assets/js/stisla.js"></script>
<script src="public/assets/js/scripts.js"></script>
<script src="public/assets/js/profile.js"></script>
<script src="public/assets/js/custom.js"></script>

