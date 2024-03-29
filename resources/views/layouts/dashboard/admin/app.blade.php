<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <!-- Material Kit CSS -->
    <link href="{{ asset('css/dashboard/material-dashboard.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/dashboard/jquery.datetimepicker.css') }}" rel="stylesheet" />

</head>

<body>
<div class="wrapper ">
    <div class="sidebar" data-color="danger" data-background-color="white" data-image="{{asset('img/dashboard/sidebar-1.jpg')}}">
        <!--
        Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

        Tip 2: you can also add an image using data-image tag
    -->
        <div class="logo">
            <a href="/" class="simple-text logo-normal">
                Web4Pro
            </a>
        </div>
        <div class="sidebar-wrapper">
            <ul class="nav">
                <li class="nav-item {{request()->routeIs('admin.dashboard') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('admin.dashboard')}}">
                        <i class="material-icons">dashboard</i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item {{ (Request::is('admin/members*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.members.index')}}">
                        <i class="material-icons">group</i>
                        <p>Members</p>
                    </a>
                </li>
                <li class="nav-item {{ (Request::is('admin/departments*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.departments.index')}}">
                        <i class="material-icons">group_work</i>
                        <p>Departments</p>
                    </a>
                </li>
                <li class="nav-item {{ (Request::is('admin/positions*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.positions.index')}}">
                        <i class="material-icons">rowing</i>
                        <p>Positions</p>
                    </a>
                </li>
                <li class="nav-item {{ (Request::is('admin/certificates*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.certificates.index')}}">
                        <i class="material-icons">verified</i>
                        <p>Certifications</p>
                    </a>
                </li>
                <li class="nav-item {{ (Request::is('admin/solutions*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.solutions.index')}}">
                        <i class="material-icons">note</i>
                        <p>Solutions</p>
                    </a>
                </li>
                <li class="nav-item {{ (Request::is('admin/categories*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.categories.index')}}">
                        <i class="material-icons">category</i>
                        <p>Categories</p>
                    </a>
                </li>

                <li class="nav-item {{ (Request::is('admin/tags*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.tags.index')}}">
                        <i class="material-icons">tag_faces</i>
                        <p>Tags</p>
                    </a>
                </li>

                <li class="nav-item {{ (Request::is('admin/links*')) ? 'active' : '' }}">
                    <a class="nav-link" href="{{route('admin.links.index')}}">
                        <i class="fa fa-external-link"></i>
                        <p>Links</p>
                    </a>
                </li>

                {{--<li class="nav-item ">--}}
                    {{--<a class="nav-link" href="{{url('/  admin/members')}}">--}}
                        {{--<i class="material-icons">person</i>--}}
                        {{--<p>Roles</p>--}}
                    {{--</a>--}}
                {{--</li>--}}
                {{--<li class="nav-item ">--}}
                    {{--<a class="nav-link" href="./tables.html">--}}
                        {{--<i class="material-icons">content_paste</i>--}}
                        {{--<p>Table List</p>--}}
                    {{--</a>--}}
                {{--</li>--}}
                <!-- your sidebar here -->
            </ul>
        </div>
    </div>
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <a class="navbar-brand" href="#pablo">Dashboard</a>
                </div>
                <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#pablo">
                                <i class="material-icons">dashboard</i>
                                <p class="d-lg-none d-md-block">
                                    Stats
                                </p>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="http://example.com" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="material-icons">notifications</i>
                                <span class="notification">5</span>
                                <p class="d-lg-none d-md-block">
                                    Some Actions
                                </p>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                                <a class="dropdown-item" href="#">Mike John responded to your email</a>
                                <a class="dropdown-item" href="#">You have 5 new tasks</a>
                                <a class="dropdown-item" href="#">You're now friend with Andrew</a>
                                <a class="dropdown-item" href="#">Another Notification</a>
                                <a class="dropdown-item" href="#">Another One</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#pablo" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="material-icons">person</i>
                                <p class="d-lg-none d-md-block">
                                    Account
                                </p>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                                <a class="dropdown-item" href="#">Profile</a>
                                <a class="dropdown-item" href="#">Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">Log out</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="content">
            <div class="container-fluid">
                <!-- your content here -->
                @yield('content')
            </div>
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <nav class="float-left">
                    <ul>
                        <li>
                            <a href="https://web4pro.com">
                                Web4Pro
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="copyright float-right">
                    &copy;
                    <script>
                        document.write(new Date().getFullYear())
                    </script>
                </div>
                <!-- your footer here -->
            </div>
        </footer>
    </div>
</div>
<!--   Core JS Files   -->
<script src="{{ asset('js/dashboard/core/jquery.min.js') }}"></script>
<script src="{{ asset('js/dashboard/core/popper.min.js') }}"></script>
<script src="{{ asset('js/dashboard/core/bootstrap-material-design.min.js') }}"></script>
<script src="{{ asset('js/dashboard/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
<script src="{{ asset('js/dashboard/moment.min.js') }}"></script>
<script src="{{ asset('js/dashboard/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/dashboard/material-dashboard.js') }}"></script>
</body>

</html>
