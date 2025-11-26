<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link href="{{ asset('theme/assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/assets/plugins/font-awesome/css/all.min.css') }}" rel="stylesheet">
    <style>
        body {font-family: 'Montserrat', sans-serif;}
        .connect-container {min-height: 100vh;}
        .page-sidebar {width: 260px; background: #121826; color: #fff;}
        .page-container {flex: 1; background: #f5f6f8;}
        .logo-box {padding: 16px; font-weight: 700;}
        .accordion-menu {list-style: none; margin: 0; padding: 0 8px;}
        .accordion-menu li a {display: block; padding: 10px 12px; color: #cbd5e0; text-decoration: none; border-radius: 6px;}
        .accordion-menu li a.active {color: #10b981;}
        .accordion-menu li a:hover {color: #a5b4fc; background: #111827;}
        .page-header {background: #fff; border-bottom: 1px solid #e5e7eb;}
        .page-content {padding: 16px; flex: 1;}
        .page-footer {background: #fff; border-top: 1px solid #e5e7eb; padding: 12px 16px; margin-top: auto;}
    </style>
    @stack('styles')
    <link href="{{ asset('theme/assets/css/connect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/assets/css/dark_theme.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/assets/css/custom.css') }}" rel="stylesheet">
    <script>
        window.APP_NAME = {{ json_encode(config('app.name')) }}
    </script>
</head>
<body>
<div class="connect-container align-content-stretch d-flex flex-wrap">
    <div class="page-sidebar">
        <div class="logo-box"><a href="{{ url('/admin') }}" class="logo-text">{{ config('app.name') }}</a></div>
        <div class="page-sidebar-inner">
            <ul class="accordion-menu">
                <li><a href="{{ url('/admin') }}" class="@yield('menu.dashboard')"><i class="material-icons-outlined">dashboard</i> Dashboard</a></li>
                <li><a href="{{ route('admin.schedules.index') }}" class="@yield('menu.schedules')"><i class="material-icons-outlined">calendar_today</i> Jadwal</a></li>
                <li><a href="{{ route('admin.schedules.import') }}" class="@yield('menu.schedules_import')"><i class="material-icons-outlined">cloud_upload</i> Upload Jadwal</a></li>
                <li><a href="{{ route('admin.attendance_jobs.index') }}" class="@yield('menu.jobs')"><i class="material-icons">done</i> Jobs</a></li>
                <li><a href="{{ route('admin.logs.whatsapp') }}" class="@yield('menu.logs_whatsapp')"><i class="material-icons">receipt_long</i> Log WA</a></li>
                <li><a href="{{ route('admin.settings.edit') }}" class="@yield('menu.settings')"><i class="material-icons">settings</i> Setting</a></li>
                <li><a href="{{ route('admin.sessions.index') }}" class="@yield('menu.sessions')"><i class="material-icons">qr_code_2</i> Sesi WA</a></li>
                <li class="sidebar-title">Master</li>
                <li><a href="{{ route('admin.work_places.index') }}" class="@yield('menu.work_places')"><i class="material-icons">place</i> Tempat</a></li>
                <li><a href="{{ route('admin.employees.index') }}" class="@yield('menu.employees')"><i class="material-icons">group</i> Karyawan</a></li>
                <li><a href="{{ route('admin.shifts.index') }}" class="@yield('menu.shifts')"><i class="material-icons">schedule</i> Shift</a></li>
                <li><a href="{{ route('admin.shift_time_rules.index') }}" class="@yield('menu.shift_time_rules')"><i class="material-icons">watch_later</i> Aturan Jam</a></li>
            </ul>
        </div>
    </div>
    <div class="page-container d-flex flex-column">
        <div class="page-header">
            <nav class="navbar navbar-expand">
                <ul class="navbar-nav">
                    <li class="nav-item"><span class="nav-link">{{ config('app.name') }}</span></li>
                </ul>
                <div class="ml-auto navbar-nav">
                    <span class="nav-link">{{ now()->toDateTimeString() }}</span>
                </div>
            </nav>
        </div>
        <div class="page-content">
            @yield('content')
        </div>
        <div class="page-footer">
            <div class="row"><div class="col-md-12"><span class="footer-text">&copy; {{ date('Y') }} {{ config('app.name') }}</span></div></div>
        </div>
    </div>
</div>
<script src="{{ asset('theme/assets/plugins/jquery/jquery-3.4.1.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/bootstrap/popper.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/blockui/jquery.blockUI.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/flot/jquery.flot.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/flot/jquery.flot.time.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/flot/jquery.flot.symbol.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/flot/jquery.flot.resize.min.js') }}"></script>
<script src="{{ asset('theme/assets/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
<script src="{{ asset('theme/assets/js/connect.min.js') }}"></script>
@stack('scripts')
</body>
</html>
