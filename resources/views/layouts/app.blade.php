<!doctype html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?> dir="rtl" <?php } ?>>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo-small.png') }}">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/regular.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/solid.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/toast-master/css/jquery.toast.css')}}" rel="stylesheet">
    <link href="{{ asset('css/colors/blue.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/summernote/summernote.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    @php $app_setting = App\Models\Settings::first(); @endphp
    <script src="https://maps.googleapis.com/maps/api/js?key={{$app_setting->google_map_api_key}}&v=3.64&libraries=drawing,geometry,places"></script>

    @yield('style')

    <style type="text/css">
            /* ── Indigo Accent Color System ── */
            /* Non-sidebar elements that use admin accent color */

            .restaurant_payout_create-inner fieldset legend {
                background: #4f46e5;
            }

            a {
                color: #4f46e5;
            }

            a:hover,
            a:focus {
                color: #4338ca;
            }

            a.link:hover,
            a.link:focus {
                color: #4338ca;
            }

            html body blockquote {
                border-left: 5px solid #4f46e5;
            }

            .text-warning {
                color: #4f46e5 !important;
            }

            .text-info {
                color: #4f46e5 !important;
            }

            .btn-primary {
                background: #4f46e5;
                border: 1px solid #4f46e5;
            }

            .bg-info {
                background-color: #4f46e5 !important;
            }

            .bellow-text ul li>span {
                color: #4f46e5;
            }

            .table tr td.redirecttopage {
                color: #4f46e5;
            }

            ul.rating {
                color: #4f46e5;
            }

            .nav-tabs.card-header-tabs .nav-link.active,
            .nav-tabs.card-header-tabs .nav-link:hover {
                background: #4f46e5;
                border-color: #4f46e5 #4f46e5 #fff;
            }

            .btn-warning,
            .btn-warning.disabled {
                background: #4f46e5;
                border: 1px solid #4f46e5;
                box-shadow: none;
            }

            .payment-top-tab .nav-tabs.card-header-tabs .nav-link.active,
            .payment-top-tab .nav-tabs.card-header-tabs .nav-link:hover {
                border-color: #4f46e5;
            }

            .nav-tabs.card-header-tabs .nav-link span.badge-success {
                background: #4f46e5;
            }

            .topbar ul.dropdown-user li a:hover {
                color: #4f46e5;
            }

            [type="checkbox"]:checked+label::before {
                border-right: 2px solid #4f46e5;
                border-bottom: 2px solid #4f46e5;
            }

            .btn-primary:hover,
            .btn-primary.disabled:hover {
                background: #4338ca;
                border: 1px solid #4338ca;
            }

            .btn-primary.active,
            .btn-primary:active,
            .btn-primary:focus,
            .btn-primary.disabled.active,
            .btn-primary.disabled:active,
            .btn-primary.disabled:focus,
            .btn-primary.active.focus,
            .btn-primary.active:focus,
            .btn-primary.active:hover,
            .btn-primary.focus:active,
            .btn-primary:active:focus,
            .btn-primary:active:hover,
            .open>.dropdown-toggle.btn-primary.focus,
            .open>.dropdown-toggle.btn-primary:focus,
            .open>.dropdown-toggle.btn-primary:hover,
            .btn-primary.focus,
            .btn-primary:focus,
            .btn-primary:not(:disabled):not(.disabled).active:focus,
            .btn-primary:not(:disabled):not(.disabled):active:focus,
            .show>.btn-primary.dropdown-toggle:focus,
            .btn-warning:hover,
            .btn-warning.disabled:hover,
            .btn-warning.active.focus,
            .btn-warning.active:focus,
            .btn-warning.active:hover,
            .btn-warning.focus:active,
            .btn-warning:active:focus,
            .btn-warning:active:hover,
            .open>.dropdown-toggle.btn-warning.focus,
            .open>.dropdown-toggle.btn-warning:focus,
            .open>.dropdown-toggle.btn-warning:hover,
            .btn-warning.focus,
            .btn-warning:focus {
                background: #4338ca;
                border-color: #4338ca;
                box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
            }

            .language-options select option,
            .pagination>li>a.page-link:hover {
                background: #4f46e5;
            }

            .cat-slider .cat-item a.cat-link:hover,
            .cat-slider .cat-item.section-selected a.cat-link {
                border-color: #4f46e5;
            }

            .cat-slider .cat-item a.cat-link {
                border-bottom-color: #4f46e5;
            }

            .cat-slider .cat-item.section-selected a.cat-link:after {
                border-color: #4f46e5;
                background: #4f46e5;
            }

            .cat-slider {
                border-color: #4f46e5;
            }

            .business-analytics .card-box i {
                background: rgba(79, 70, 229, 0.1);
                color: #4f46e5;
            }

            .order-status .data i,
            .order-status span.count {
                color: #4f46e5;
            }

            .userlist-top-left a.nav-link {
                border-color: #4f46e5;
                color: #4f46e5;
            }

            .userlist-top-left a.nav-link:hover {
                background: #4f46e5;
                color: #fff;
            }

            .user-detail .nav.nav-tabs li a {
                border-color: #4f46e5;
                color: #4f46e5;
            }

            .user-detail .nav.nav-tabs li a:hover,
            .user-detail .nav.nav-tabs li a.active {
                background: #4f46e5;
                color: #fff;
            }

            .user-top {
                background-color: #4f46e5;
            }
    </style>

    <style type="text/css">
        /* Modern Minimalist UI & Professional Styling Overrides */
        :root {
            --primary: #4f46e5;       /* Indigo */
            --primary-hover: #4338ca;
            --sidebar-bg: #0f172a;    /* Dark Slate */
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --sidebar-active: #4f46e5;
            --sidebar-text: #94a3b8;
            --sidebar-active-text: #ffffff;
            --bg-main: #f8fafc;       /* Light cool gray */
            --card-border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        /* Page Background & Global Fonts */
        body {
            background-color: var(--bg-main) !important;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        #main-wrapper {
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Topbar Header */
        .topbar {
            position: sticky !important;
            top: 0;
            z-index: 1060;
            background: #ffffff !important;
            border-bottom: 1px solid var(--card-border);
            height: 70px !important;
            width: 100% !important;
        }

        /* ── Modern Unified Sidebar & Topbar ── */
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-hover-bg: #1e293b;
            --sidebar-hover-text: #ffffff;
            --sidebar-active-bg: #4f46e5;
            --sidebar-active-text: #ffffff;
            --topbar-bg: #ffffff;
            --topbar-border: #e2e8f0;
        }

        /* Topbar Positioning & Fixed Header */
        .topbar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 64px !important;
            z-index: 1060 !important;
            background: #ffffff !important;
            border-bottom: 1px solid var(--topbar-border) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
        }

        .topbar .top-navbar {
            height: 64px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
        }

        /* Company Brand Logo Area - Matches sidebar background exactly */
        .topbar .navbar-header {
            width: 260px !important;
            height: 64px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #0f172a !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
            flex-shrink: 0 !important;
            z-index: 1070 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .topbar .navbar-collapse {
            flex: 1 !important;
            height: 64px !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 20px !important;
            background: #ffffff !important;
        }

        /* Sidebar Container - Smooth Scrolling & Single Unified Dark Slate Background */
        .left-sidebar {
            width: 260px !important;
            height: calc(100vh - 64px) !important;
            max-height: calc(100vh - 64px) !important;
            background: #0f172a !important;
            position: fixed !important;
            top: 64px !important;
            left: 0 !important;
            bottom: 0 !important;
            z-index: 1050 !important;
            padding-top: 0 !important;
            margin-top: 0 !important;
        }

        /* Complete Scrollbar Hiding for Sidebar & Mini-Sidebar */
        .left-sidebar,
        .scroll-sidebar,
        body.mini-sidebar .left-sidebar,
        #main-wrapper.mini-sidebar .left-sidebar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        .left-sidebar::-webkit-scrollbar,
        .scroll-sidebar::-webkit-scrollbar,
        body.mini-sidebar .left-sidebar::-webkit-scrollbar,
        #main-wrapper.mini-sidebar .left-sidebar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }

        .scroll-sidebar {
            padding: 6px 0 100px 0 !important;
            margin: 0 !important;
            background: #0f172a !important;
        }

        .sidebar-nav {
            background: #0f172a !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Sidebar Items & Compact Heights */
        .sidebar-nav ul,
        .sidebar-nav ul#sidebarnav {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: #0f172a !important;
        }

        .sidebar-nav ul li,
        .sidebar-nav ul#sidebarnav > li {
            margin: 2px 10px !important;
            padding: 0 !important;
        }

        .sidebar-nav ul#sidebarnav > li:first-child {
            margin-top: 4px !important;
            padding-top: 0 !important;
        }

        .sidebar-nav ul li a {
            color: #94a3b8 !important;
            background: transparent !important;
            border-radius: 6px !important;
            padding: 8px 12px !important;
            display: flex !important;
            align-items: center !important;
            font-weight: 500 !important;
            font-size: 13px !important;
            transition: all 0.15s ease !important;
            text-decoration: none !important;
        }

        .sidebar-nav > ul > li > a i {
            display: inline-block !important;
            color: #64748b !important;
            margin-right: 10px !important;
            font-size: 16px !important;
            transition: color 0.15s ease !important;
        }

        /* Hover State */
        .sidebar-nav ul li a:hover {
            color: #ffffff !important;
            background: #1e293b !important;
        }

        .sidebar-nav ul li a:hover i {
            color: #818cf8 !important;
        }

        /* Active State */
        .sidebar-nav ul li.active > a,
        .sidebar-nav ul li a.active {
            color: #ffffff !important;
            background: #4f46e5 !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3) !important;
        }

        .sidebar-nav ul li.active > a i,
        .sidebar-nav ul li a.active i {
            color: #ffffff !important;
        }

        /* Submenus - Remove all bullets, icons, and pseudo-elements */
        .sidebar-nav ul li ul {
            background: rgba(0, 0, 0, 0.3) !important;
            padding: 4px 0 !important;
            border-radius: 6px !important;
            margin: 3px 0 !important;
        }

        .sidebar-nav ul li ul li {
            margin: 1px 4px !important;
        }

        .sidebar-nav ul li ul li a i,
        .sidebar-nav ul li ul li a::before,
        .sidebar-nav ul li ul li a::after,
        .sidebar-nav #sidebarnav ul a::before,
        .sidebar-nav #sidebarnav ul a::after {
            display: none !important;
            content: none !important;
        }

        .sidebar-nav ul li ul li a {
            padding: 7px 12px 7px 20px !important;
            font-size: 12.5px !important;
            color: #94a3b8 !important;
        }

        .sidebar-nav ul li ul li a:hover,
        .sidebar-nav ul li ul li.active a {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }

        /* ── Universal Page Container Layout (Fixes all top/bottom cutoffs across ALL admin pages) ── */
        .page-wrapper,
        #main-wrapper > div:not(.topbar):not(.left-sidebar):not(.left-sidebar-overlay) {
            margin-left: 260px !important;
            margin-top: 64px !important;
            width: calc(100% - 260px) !important;
            min-height: calc(100vh - 64px) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            background: #f8fafc !important;
            padding: 0 0 80px 0 !important;
            box-sizing: border-box !important;
        }

        /* Prevent nested container double margins */
        .page-wrapper .page-wrapper,
        #main-wrapper > div .page-wrapper {
            margin-left: 0 !important;
            margin-top: 0 !important;
            width: 100% !important;
            padding: 0 !important;
            min-height: auto !important;
        }

        /* Responsive override for min-width 768px */
        @media (min-width: 768px) {
            .page-wrapper,
            #main-wrapper > div:not(.topbar):not(.left-sidebar):not(.left-sidebar-overlay) {
                margin-left: 260px !important;
                margin-top: 64px !important;
                width: calc(100% - 260px) !important;
            }
        }

        /* Mini-Sidebar Mode (Collapsed State - Shrinks smoothly to 70px) */
        body.mini-sidebar .topbar .navbar-header,
        #main-wrapper.mini-sidebar .topbar .navbar-header {
            width: 70px !important;
        }

        body.mini-sidebar .topbar .navbar-header .brand-name,
        #main-wrapper.mini-sidebar .topbar .navbar-header .brand-name {
            display: none !important;
        }

        body.mini-sidebar .left-sidebar,
        #main-wrapper.mini-sidebar .left-sidebar {
            width: 70px !important;
        }

        body.mini-sidebar .page-wrapper,
        #main-wrapper.mini-sidebar .page-wrapper,
        body.mini-sidebar #main-wrapper > div:not(.topbar):not(.left-sidebar):not(.left-sidebar-overlay),
        #main-wrapper.mini-sidebar > div:not(.topbar):not(.left-sidebar):not(.left-sidebar-overlay) {
            margin-left: 70px !important;
            margin-top: 64px !important;
            width: calc(100% - 70px) !important;
        }

        /* Collapsed Sidebar Item Styles */
        body.mini-sidebar .sidebar-nav .hide-menu,
        #main-wrapper.mini-sidebar .sidebar-nav .hide-menu,
        body.mini-sidebar .sidebar-nav ul li a.has-arrow::after,
        #main-wrapper.mini-sidebar .sidebar-nav ul li a.has-arrow::after,
        body.mini-sidebar .sidebar-nav ul li ul,
        #main-wrapper.mini-sidebar .sidebar-nav ul li ul {
            display: none !important;
        }

        body.mini-sidebar .sidebar-nav ul li,
        #main-wrapper.mini-sidebar .sidebar-nav ul li {
            margin: 4px 6px !important;
        }

        body.mini-sidebar .sidebar-nav ul li a,
        #main-wrapper.mini-sidebar .sidebar-nav ul li a {
            padding: 10px 0 !important;
            justify-content: center !important;
            text-align: center !important;
            width: 100% !important;
        }

        body.mini-sidebar .sidebar-nav ul li a i,
        #main-wrapper.mini-sidebar .sidebar-nav ul li a i {
            margin-right: 0 !important;
            font-size: 20px !important;
            display: inline-block !important;
        }

        /* Override legacy grids for responsiveness */
        .container-fluid {
            padding: 24px 30px !important;
            flex: 1;
        }

        /* Responsive Overrides */
        @media (max-width: 991.98px) {
            .left-sidebar {
                transform: translateX(-260px);
            }
            .topbar .navbar-header {
                width: 180px !important;
            }
            #main-wrapper > .page-wrapper,
            .page-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            body.show-sidebar .left-sidebar {
                transform: translateX(0);
            }
            body.show-sidebar .left-sidebar-overlay {
                display: block !important;
            }
        }

        /* Buttons & Cards Styling */
        .card {
            border: 1px solid var(--card-border) !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
            background-color: #ffffff;
            margin-bottom: 24px;
        }

        .card-body {
            padding: 24px !important;
        }

        .btn-primary, .btn-themecolor {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 8px !important;
            padding: 8px 16px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover, .btn-themecolor:hover {
            background-color: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
        }

        .sidebartoggler, .nav-toggler {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: var(--text-dark) !important;
        }
        .badge-info {
            background-color: var(--primary) !important;
        }

        /* Cards Styling */
        .card {
            border: 1px solid var(--card-border) !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05) !important;
            background-color: #ffffff;
            margin-bottom: 24px;
            overflow: hidden;
        }
        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid var(--card-border) !important;
            padding: 16px 20px;
        }
        .card-title {
            color: var(--text-dark) !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            margin: 0 !important;
        }

        /* ── Master Premium Table & Badge Styling ── */
        .table, 
        table.dataTable,
        .table-bordered,
        .table-striped,
        .stylish-table,
        .display {
            border: none !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
        }

        .table th,
        .table-bordered th,
        table.dataTable thead th,
        .stylish-table th,
        .display th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 12px 16px !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .table td,
        .table-bordered td,
        table.dataTable tbody td,
        .stylish-table td,
        .display td {
            padding: 12px 16px !important;
            vertical-align: middle !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            color: #334155 !important;
            font-size: 13px !important;
            background-color: #ffffff !important;
        }

        .table tbody tr:hover td,
        .table-striped tbody tr:hover td,
        .table-hover tbody tr:hover td,
        table.dataTable tbody tr:hover td,
        .stylish-table tbody tr:hover td {
            background-color: #f8fafc !important;
            border-left: none !important;
        }

        /* Master Premium Badges */
        .badge {
            font-size: 11px !important;
            font-weight: 600 !important;
            padding: 4px 10px !important;
            border-radius: 20px !important;
            letter-spacing: 0.2px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            line-height: 1.2 !important;
        }

        .badge-success, .badge-outline-success {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }

        .badge-danger, .badge-outline-danger {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca !important;
        }

        .badge-warning, .badge-outline-warning {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }

        .badge-info, .badge-primary, .badge-outline-info, .badge-outline-primary {
            background-color: #e0e7ff !important;
            color: #4338ca !important;
            border: 1px solid #c7d2fe !important;
        }

        .badge-secondary, .badge-dark, .badge-outline-secondary {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* Action Buttons Gap Container */
        td.action-btn, td .action-btn, td .d-flex {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        /* Tabs Styling */
        .nav-tabs {
            border-bottom: 1px solid var(--card-border) !important;
        }
        .nav-tabs .nav-link {
            border: none !important;
            color: var(--text-muted) !important;
            font-weight: 600;
            padding: 12px 20px;
            border-bottom: 2px solid transparent !important;
        }
        .nav-tabs .nav-link.active, .nav-tabs .nav-link:hover {
            color: var(--primary) !important;
            background: transparent !important;
            border-bottom: 2px solid var(--primary) !important;
        }

        /* Compact Admin Panel Global Refinements */
        body {
            font-size: 13px !important;
            color: #334155 !important;
        }

        .page-titles {
            padding: 12px 24px !important;
            margin-bottom: 16px !important;
            background: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .page-titles h3 {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 !important;
        }
        .breadcrumb {
            padding: 0 !important;
            margin: 0 !important;
            font-size: 12px !important;
            background: transparent !important;
        }

        .form-group {
            margin-bottom: 14px !important;
        }
        label, .form-label, .control-label {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            margin-bottom: 4px !important;
        }
        .form-control, select.form-control, input.form-control {
            height: 36px !important;
            line-height: 1.4 !important;
            padding: 6px 12px !important;
            font-size: 13px !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
        }
        #global-header-search,
        input#global-header-search.form-control {
            padding-left: 42px !important;
            padding-right: 15px !important;
            height: 38px !important;
            border-radius: 20px !important;
        }
        select.form-control {
            padding-right: 28px !important;
            appearance: auto !important;
        }
        textarea.form-control {
            height: auto !important;
            padding: 8px 12px !important;
        }
        .input-group-text {
            font-size: 13px !important;
            padding: 6px 12px !important;
            height: 36px !important;
            display: flex !important;
            align-items: center !important;
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        .btn {
            font-size: 13px !important;
            font-weight: 600 !important;
            padding: 6px 14px !important;
            height: 36px !important;
            border-radius: 6px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            margin-right: 6px !important;
            transition: all 0.15s ease !important;
        }
        .btn:last-child {
            margin-right: 0 !important;
        }
        .btn-sm {
            font-size: 12px !important;
            padding: 4px 10px !important;
            height: 30px !important;
        }
        .btn-action-container, .btn-group {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .table th {
            padding: 10px 12px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            background: #f8fafc !important;
            color: #475569 !important;
            border-bottom: 1px solid #cbd5e1 !important;
        }
        .table td {
            padding: 8px 12px !important;
            font-size: 13px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            margin-bottom: 16px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03) !important;
        }
        .card-body {
            padding: 16px 20px !important;
        }
        .card-header {
            padding: 12px 20px !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
    </style>

    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{asset('assets/plugins/bootstrap/css/bootstrap-rtl.min.css')}}" rel="stylesheet">
    <?php } ?>

    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{asset('css/style_rtl.css')}}" rel="stylesheet">
    <?php } ?>

</head>

<body>

    <div id="app">
        <div id="main-wrapper">
            <!-- Topbar Header -->
            <header class="topbar non-printable">
                <nav class="navbar top-navbar navbar-expand-md navbar-light">
                    @include('layouts.header')
                </nav>
            </header>

            <!-- Sidebar -->
            <aside class="left-sidebar non-printable">
                <div class="scroll-sidebar">
                    @include('layouts.menu')
                </div>
            </aside>
            
            <!-- Mobile Sidebar Overlay -->
            <div class="left-sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 1040;"></div>

            <!-- Page Content -->
            @yield('content')
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('js/waves.js') }}"></script>
    <script src="{{ asset('js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js')}}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/summernote/summernote.js')}}"></script>
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="text/javascript">
        
        jQuery(window).scroll(function() {
            var scroll = jQuery(window).scrollTop();
            if (scroll <= 60) {
                jQuery("body").removeClass("sticky");
            } else {
                jQuery("body").addClass("sticky");
            }
        });

        function setCookie(cname, cvalue, exdays) {
            const d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            let expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        function getCookie(cname) {
            let name = cname + "=";
            let ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        $(document).ready(function() {
            var url = "{{ route('language.header') }}";
            $.ajax({
                url: url,
                type: "GET",
                data: {
                    _token: '{{csrf_token()}}',
                },

                dataType: 'json',
                success: function(data) {
                    $.each(data, function(key, value) {
                        $('#language_dropdown').append($("<option></option>").attr("value", value.code).text(value.language));
                        //append('<option value="' + value.id + '">' + value.language + '</option>');
                    });
                    <?php if (session()->get('locale')) { ?>
                        $("#language_dropdown").val("<?php echo session()->get('locale'); ?>");
                    <?php } ?>
                }
            });


        });

        var url1 = "{{ route('changeLang') }}";

        $(".changeLang").change(function() {
            var slug = $(this).val();
            var url = "{{ route('lang.code',':slugid') }}";
            url = url.replace(':slugid', slug);
            if (slug) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: '{{csrf_token()}}',
                    },

                    dataType: 'json',
                    success: function(data) {

                        $.each(data, function(key, value) {
                            if (value.code == slug) {
                                if (value.is_rtl == false) {
                                    setCookie('is_rtl', 'false', 365);
                                } else {
                                    setCookie('is_rtl', value.is_rtl.toString(), 365);
                                }
                                window.location.href = url1 + "?lang=" + value.code;
                            }
                        });
                    }
                });
            }

        });

        // Clear legacy cookie color so custom indigo theme takes effect
        setCookie('admin_panel_color', '#4f46e5', 365);
    </script>
    </script>
    
    <script>
        $(document).ready(function() {
            // Responsive Sidebar toggler
            $(document).on("click", ".sidebartoggler, .nav-toggler", function(e) {
                e.preventDefault();
                $("body").toggleClass("show-sidebar");
                $("#main-wrapper").toggleClass("mini-sidebar");
            });

            // Close sidebar when overlay is clicked on mobile
            $(document).on("click", ".left-sidebar-overlay", function() {
                $("body").removeClass("show-sidebar");
            });
        });
    </script>

    @yield('scripts')

</body>

</html>