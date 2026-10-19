<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title') @yield('title') - HR System @else نظام الموارد البشرية - HR System @endif</title>

    {{-- Favicon & Tab Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0f172a">
    <link href="{{ asset('assets/css/flowbite.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/flatpickr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/tom-select.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="{{ asset('assets/js/flatpickr.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')

    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        img, table, iframe, canvas, svg {
            max-width: 100%;
        }

        /* --- Modern Premium Nav --- */
        .main-nav {
            background: #0f172a;
            color: #fff;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 64px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 100%;
        }

        .nav-inner {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            gap: 1rem;
        }

        .nav-logo {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-shrink: 0;
        }

        .nav-logo-img {
            height: 38px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.3));
            transition: transform 0.2s ease;
        }

        .nav-logo:hover .nav-logo-img {
            transform: scale(1.06);
        }

        .nav-logo span {
            background: #2563eb;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0;
            padding: 0;
            list-style: none;
            height: 100%;
        }

        .nav-item {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
        }

        .nav-link {
            color: #94a3b8;
            padding: 0.5rem 0.7rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }

        .nav-link.active {
            color: #3b82f6;
        }

        /* Dropdown Logic */
        .dropdown {
            position: relative;
        }

        .dropdown-trigger {
            cursor: pointer;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 0.75rem;
            min-width: 210px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s;
            z-index: 1001;
            display: grid;
            gap: 0.15rem;
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }
        .dropdown-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }
        .dropdown-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
        }
        .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .dropdown-link {
            color: #94a3b8;
            padding: 0.55rem 0.75rem;
            border-radius: 6px;
            font-size: 0.82rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            text-align: right;
        }

        .dropdown-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .dropdown-link.active {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .nav-group-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
            padding: 0.4rem 0.75rem 0.2rem;
            margin-top: 0.3rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            font-weight: 800;
        }

        .nav-group-label:first-child {
            border-top: none;
            margin-top: 0;
        }

        .btn-logout {
            padding: 0.4rem 0.8rem;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: #fff !important;
        }

        .nav-separator {
            width: 1px;
            height: 24px;
            background: rgba(255,255,255,0.1);
            margin: 0 0.4rem;
        }

        /* Mobile Adjustments */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            cursor: pointer;
            gap: 4px;
        }

        .hamburger span {
            display: block;
            width: 18px;
            height: 2px;
            background: #fff;
            border-radius: 2px;
            transition: 0.3s;
            transform-origin: center;
        }

        .hamburger.open span:nth-child(1) {
            transform: translateY(6px) rotate(45deg);
        }

        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.open span:nth-child(3) {
            transform: translateY(-6px) rotate(-45deg);
        }

        .mobile-nav-right {
            display: none;
            align-items: center;
            gap: 0.5rem;
        }

        .mobile-notification-btn {
            font-size: 1.1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            width: 40px;
            height: 40px;
            transition: 0.3s;
        }

        .mobile-notification-btn:active {
            background: rgba(255,255,255,0.15);
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: #fff;
            font-size: 0.65rem;
            font-weight: bold;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid #0f172a;
        }

        .desktop-badge {
            background: #ef4444;
            color: #fff;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 0.1rem 0.4rem;
            border-radius: 12px;
            margin-right: 0.3rem;
        }

        .mobile-menu {
            display: none;
            flex-direction: column;
            background: #0f172a;
            position: fixed;
            top: 64px;
            left: 0;
            right: 0;
            bottom: 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 1rem 1rem 5rem;
            gap: 0.3rem;
            z-index: 999;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .mobile-menu.open {
            display: flex;
        }

        .mobile-menu a, .mobile-menu button {
            color: #94a3b8;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: right;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mobile-menu a.active {
            background: #2563eb;
            color: #fff;
        }

        .mobile-separator {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 0.5rem 0;
        }

        @media (max-width: 1200px) {
            .nav-link { padding: 0.5rem 0.5rem; font-size: 0.8rem; }
        }

        @media (max-width: 1024px) {
            .nav-links { display: none; }
            .hamburger { display: flex; }
            .mobile-nav-right { display: flex; }
            .mobile-current-page {
                display: flex;
                align-items: center;
            }
            .mobile-current-page-text {
                background: rgba(255,255,255,0.05);
                color: #fff;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 700;
            }
        }

        .page-content {
            width: 100%;
            max-width: 100%;
            padding: 14px;
            overflow-x: hidden;
            min-height: calc(100vh - 165px);
        }

        .footer-bar {
            width: 100%;
            background: #1e3a5f;
            color: #e2e8f0;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: 18px;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 14px 16px;
            text-align: center;
            font-size: 0.92rem;
            line-height: 1.8;
        }

        .footer-inner a {
            color: #93c5fd;
            text-decoration: none;
        }

        .footer-inner a:hover {
            text-decoration: underline;
        }

        @media (min-width: 768px) {
            .page-content {
                padding: 20px;
            }
        }

        @media (max-width: 900px) {
            .nav-inner {
                min-height: 56px;
                padding: 0 0.85rem;
                display: grid;
                grid-template-columns: auto 1fr auto;
                align-items: center;
                gap: 0.5rem;
            }

            .nav-links {
                display: none;
            }

            .hamburger {
                display: flex;
            }

            .nav-logo {
                font-size: 0.98rem;
                gap: 0.4rem;
            }

            .nav-logo-img {
                height: 30px;
            }

            .nav-logo span {
                font-size: 0.72rem;
                padding: 2px 8px;
            }

            .mobile-current-page {
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 0;
                flex: 1;
            }

            .mobile-current-page-text {
                max-width: 100%;
            }
        }

        @media (max-width: 576px) {
            .nav-inner {
                min-height: 54px;
                padding: 0 0.75rem;
            }

            .nav-logo {
                font-size: 0.92rem;
            }

            .nav-logo-img {
                height: 26px;
            }

            .nav-logo span {
                font-size: 0.68rem;
                padding: 2px 7px;
            }

            .mobile-menu {
                padding: 0.55rem 0.7rem 0.8rem;
            }

            .mobile-menu a,
            .mobile-menu button {
                font-size: 0.9rem;
                padding: 0.78rem 0.85rem;
            }

            .mobile-current-page-text {
                font-size: 0.8rem;
                padding: 0.32rem 0.65rem;
                max-width: 135px;
            }

            .page-content {
                padding: 12px;
            }

            .footer-inner {
                font-size: 0.84rem;
                padding: 12px 14px;
            }
        }

        @media (min-width: 901px) {
            .mobile-current-page {
                display: none !important;
            }
        }

        /* Global Loader Styles */
        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.7); /* slate-900 with opacity */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }

        #global-loader.show {
            display: flex;
        }

        .loader-z {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(37, 99, 235, 0.5));
        }

        .loader-z path {
            stroke: #2563eb;
            stroke-width: 8;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            stroke-dasharray: 300;
            stroke-dashoffset: 300;
            animation: drawZ 2s ease-in-out infinite;
        }

        @keyframes drawZ {
            0% { stroke-dashoffset: 300; }
            50% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: -300; }
        }

        .loader-text {
            color: #fff;
            font-family: 'Cairo', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(0.98); }
        }

        /* Tom Select RTL Fixes */
        html[dir="rtl"] .ts-wrapper.single .ts-control {
            padding-left: 2rem !important;
            padding-right: 0.75rem !important;
            background-position: left 0.75rem center !important;
            text-align: right !important;
        }

        html[dir="rtl"] .ts-wrapper.single .ts-control::after {
            left: 12px !important;
            right: auto !important;
        }

        html[dir="rtl"] .ts-dropdown {
            text-align: right !important;
        }

        /* Adjusting for Bootstrap/Tailwind conflict */
        .ts-wrapper .ts-control {
            min-height: 38px;
            display: flex;
            align-items: center;
            border-radius: 0.5rem;
            border-color: #d1d5db;
        }

        .ts-wrapper.focus .ts-control {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            border-color: #2563eb;
        }
    </style>
</head>
<body>
    <!-- Global Loader Overlay -->
    <div id="global-loader">
        <svg class="loader-z" viewBox="0 0 100 100">
            <path d="M 25 25 L 75 25 L 25 75 L 75 75" />
        </svg>
        <div class="loader-text">جاري التحميل...</div>
    </div>

@php
    $currentPageLabel = match(true) {
        request()->routeIs('leave') => 'اجازات',
        request()->routeIs('permission') => 'اذونات',
        request()->routeIs('Overtime') => 'اضافي',
        request()->routeIs('view_Overtime') => 'عرض الاضافي',
        request()->routeIs('view_leave') => 'عرض الاجازات',
        request()->routeIs('view_permission') => 'عرض الاذونات',
        request()->routeIs('check_in_out') => 'حضور وانصراف',
        request()->routeIs('view_check_in_out') => 'سجل الحضور',
        request()->routeIs('login') => 'تسجيل الدخول',
        request()->routeIs('register') => 'إنشاء حساب',
        request()->routeIs('view_penalties') => 'الجزاءات',
        request()->routeIs('admin.penalties') => 'إضافة جزاء',
        request()->routeIs('settlements.create') => 'طلب تسوية',
        request()->routeIs('settlements.index') => 'سجل التسويات',
        request()->routeIs('incentives.create') => 'إضافة حافز',
        request()->routeIs('incentives.index') => 'عرض الحوافز',
        request()->routeIs('admin_notes.create') => 'إضافة ملاحظة',
        request()->routeIs('admin_notes.index') => 'ملاحظات الإدارة',
        request()->routeIs('instructions.index') => 'التعليمات والتنبيهات',
        request()->routeIs('super_admin.entry') => 'إضافة يدوية',
        request()->routeIs('rosters.*') => 'جدول النوبتجيات',
        request()->routeIs('audit_log') => 'Audit Log',
        request()->routeIs('full_report.index') => 'تقارير مجمعه',
        request()->routeIs('employee_profiles.*') => 'ملفات الموظفين',
        request()->routeIs('developer.roles.*') => 'إدارة الفئات والصلاحيات',
        request()->routeIs('developer.shifts.*') => 'مجموعات الشيفتات',
        request()->routeIs('developer.settings.*') => 'إعدادات النظام العامة',
        request()->routeIs('my_profile') => 'بروفايلي الشخصي',
        request()->routeIs('notifications.index') => 'الإشعارات',
        default => 'الرئيسية',
    };

    $unreadCount = Auth::check() ? Auth::user()->unreadNotifications->count() : 0;
@endphp

<nav class="main-nav">
    <div class="nav-inner">
        <a href="/" class="nav-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="nav-logo-img">
            <span>HR</span> System
        </a>

        <div class="mobile-current-page">
            <span class="mobile-current-page-text">{{ $currentPageLabel }}</span>
        </div>

        @auth
        <div class="nav-links">
            <div class="nav-item"><a href="{{ route('my_profile') }}" class="nav-link {{ request()->routeIs('my_profile') ? 'active' : '' }}">👤 {{ Auth::user()->name }}</a></div>
            <div class="nav-separator"></div>
            {{-- طلبات --}}
            <div class="nav-item"><a href="{{ route('leave') }}" class="nav-link {{ request()->routeIs('leave') ? 'active' : '' }}">🏝️ اجازات</a></div>
            <div class="nav-item"><a href="{{ route('permission') }}" class="nav-link {{ request()->routeIs('permission') ? 'active' : '' }}">📋 اذونات</a></div>
            <div class="nav-item"><a href="{{ route('Overtime') }}" class="nav-link {{ request()->routeIs('Overtime') ? 'active' : '' }}">⏱️ اضافي</a></div>
            <div class="nav-item"><a href="{{ route('check_in_out') }}" class="nav-link {{ request()->routeIs('check_in_out') ? 'active' : '' }}">⏳ حضور وانصراف</a></div>
            <div class="nav-item"><a href="{{ route('settlements.create') }}" class="nav-link {{ request()->routeIs('settlements.create') ? 'active' : '' }}">⚙️ تسوية</a></div>
            
            @can('rosters.view')
            <div class="nav-item"><a href="{{ route('rosters.index') }}" class="nav-link {{ request()->routeIs('rosters.*') ? 'active' : '' }}">📅 النوبتجيات</a></div>
            @endcan

            @can('instructions.view')
            <div class="nav-item"><a href="{{ route('instructions.index') }}" class="nav-link {{ request()->routeIs('instructions.index') ? 'active' : '' }}">📢 التعليمات</a></div>
            @endcan

            <div class="nav-item"><a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                🔔 الإشعارات
                @if($unreadCount > 0)
                    <span class="desktop-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a></div>

            <div class="nav-separator"></div>

            {{-- تتبع السجلات (Dropdown) --}}
            <div class="nav-item dropdown">
                <div class="nav-link dropdown-trigger">
                    📊 متابعة السجلات
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="dropdown-menu">
                    <a href="{{ route('view_Overtime') }}" class="dropdown-link {{ request()->routeIs('view_Overtime') ? 'active' : '' }}">📈 سجل الاضافي</a>
                    <a href="{{ route('view_leave') }}" class="dropdown-link {{ request()->routeIs('view_leave') ? 'active' : '' }}">🏖️ سجل الاجازات</a>
                    <a href="{{ route('view_permission') }}" class="dropdown-link {{ request()->routeIs('view_permission') ? 'active' : '' }}">📝 سجل الاذونات</a>
                    <a href="{{ route('view_check_in_out') }}" class="dropdown-link {{ request()->routeIs('view_check_in_out') ? 'active' : '' }}">📂 سجل الحضور</a>
                    <a href="{{ route('view_penalties') }}" class="dropdown-link {{ request()->routeIs('view_penalties') ? 'active' : '' }}">⚠️ سجل الجزاءات</a>
                    <a href="{{ route('settlements.index') }}" class="dropdown-link {{ request()->routeIs('settlements.index') ? 'active' : '' }}">⚙️ سجل التسويات</a>
                    @can('rosters.view')
                    <a href="{{ route('rosters.index') }}" class="dropdown-link {{ request()->routeIs('rosters.*') ? 'active' : '' }}">📅 جدول النوبتجيات والشيفتات</a>
                    @endcan
                </div>
            </div>

            {{-- أدوات الإدارة (Admin/Super Admin) --}}
            @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
            <div class="nav-item dropdown">
                <div class="nav-link dropdown-trigger">
                    🛠️ الإدارة
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="dropdown-menu">
                    @can('penalties.create')
                    <div class="nav-group-label">إدارة الجزاءات</div>
                    <a href="{{ route('admin.penalties') }}" class="dropdown-link {{ request()->routeIs('admin.penalties') ? 'active' : '' }}">⚠️ إضافة جزاء</a>
                    @endcan
                    
                    @if(Auth::user()->can('employees.view_profiles') || Auth::user()->can('reports.full_report') || Auth::user()->can('employees.reset_password') || Auth::user()->can('employees.manual_entry'))
                        <div class="nav-group-label">إدارة الموظفين والتقارير</div>
                        
                        @can('employees.reset_password')
                        <a href="{{ route('admin.reset_password') }}" class="dropdown-link {{ request()->routeIs('admin.reset_password') ? 'active' : '' }}">🔑 استعادة كلمة السر</a>
                        @endcan
                        
                        @can('employees.manual_entry')
                        <a href="{{ route('super_admin.entry') }}" class="dropdown-link {{ request()->routeIs('super_admin.entry') ? 'active' : '' }}">➕ الإضافة اليدويه</a>
                        @endcan

                        @can('reports.full_report')
                        <a href="{{ route('full_report.index') }}" class="dropdown-link {{ request()->routeIs('full_report.index') ? 'active' : '' }}">📄 التقرير الشامل</a>
                        @endcan
                        
                        @can('employees.view_profiles')
                        <a href="{{ route('employee_profiles.index') }}" class="dropdown-link {{ request()->routeIs('employee_profiles.*') ? 'active' : '' }}">👤 ملفات الموظفين</a>
                        @endcan
                    @endif

                    @if(Auth::user()->can('incentives.create') || Auth::user()->can('admin_notes.create') || Auth::user()->can('incentives.view') || Auth::user()->can('admin_notes.view') || Auth::user()->can('instructions.view'))
                        <div class="nav-group-label">الحوافز والملاحظات</div>
                        @can('incentives.create')
                        <a href="{{ route('incentives.create') }}" class="dropdown-link {{ request()->routeIs('incentives.create') ? 'active' : '' }}">🌟 إضافة حافز</a>
                        @endcan
                        @can('incentives.view')
                        <a href="{{ route('incentives.index') }}" class="dropdown-link {{ request()->routeIs('incentives.index') ? 'active' : '' }}">📈 عرض الحوافز</a>
                        @endcan
                        @can('admin_notes.create')
                        <a href="{{ route('admin_notes.create') }}" class="dropdown-link {{ request()->routeIs('admin_notes.create') ? 'active' : '' }}">🗒️ إضافة ملاحظة</a>
                        @endcan
                        @can('admin_notes.view')
                        <a href="{{ route('admin_notes.index') }}" class="dropdown-link {{ request()->routeIs('admin_notes.index') ? 'active' : '' }}">🗒️ ملاحظات الإدارة</a>
                        @endcan
                    @endif

                    @if(Auth::user()->email === 'z@z.z' || Auth::user()->email === 'mtaha@gama.com')
                        <div class="nav-group-label">Security Logs</div>
                        <a href="{{ route('audit_log') }}" class="dropdown-link {{ request()->routeIs('audit_log') ? 'active' : '' }}">📜 Audit Log</a>
                    @endif

                    @developer
                        <div class="nav-group-label">منطقة المطور</div>
                        <a href="{{ route('developer.settings.index') }}" class="dropdown-link {{ request()->routeIs('developer.settings.*') ? 'active' : '' }}">⚙️ إعدادات النظام</a>
                        <a href="{{ route('developer.roles.index') }}" class="dropdown-link {{ request()->routeIs('developer.roles.*') ? 'active' : '' }}">🛡️ الفئات والصلاحيات</a>
                        <a href="{{ route('developer.shifts.index') }}" class="dropdown-link {{ request()->routeIs('developer.shifts.*') ? 'active' : '' }}">⏰ مجموعات الشيفتات</a>
                    @enddeveloper
                </div>
            </div>
            @endif

            <div class="nav-separator"></div>

            <div class="nav-item">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">🚪 خروج</button>
                </form>
            </div>
        </div>
        @else
        <div class="nav-links">
            <a href="{{ route('login') }}" class="nav-link btn-login">🔑 دخول</a>
            <!-- <span class="nav-link text-muted" style="cursor: not-allowed; opacity: 0.5;">✨ حساب جديد</span> -->
        </div>
        @endauth
        
        <div class="mobile-nav-right">
            @auth
            <a href="{{ route('notifications.index') }}" class="mobile-notification-btn" aria-label="Notifications" style="position: relative;">
                🔔
                @if($unreadCount > 0)
                    <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>
            @endauth
            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu" type="button">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    
    <div class="mobile-menu" id="mobileMenu">
        @auth
            <a href="{{ route('my_profile') }}" class="{{ request()->routeIs('my_profile') ? 'active' : '' }}">👤 {{ Auth::user()->name }}</a>
            <div class="mobile-separator"></div>
            <a href="{{ route('leave') }}" class="{{ request()->routeIs('leave') ? 'active' : '' }}">🏖️ اجازات</a>
            <a href="{{ route('permission') }}" class="{{ request()->routeIs('permission') ? 'active' : '' }}">📋 اذونات</a>
            <a href="{{ route('Overtime') }}" class="{{ request()->routeIs('Overtime') ? 'active' : '' }}">⏱️ اضافي</a>
            <a href="{{ route('check_in_out') }}" class="{{ request()->routeIs('check_in_out') ? 'active' : '' }}">⏳ حضور وانصراف</a>
            <a href="{{ route('settlements.create') }}" class="{{ request()->routeIs('settlements.create') ? 'active' : '' }}">⚙️ طلب تسوية</a>
            
            @can('instructions.view')
            <a href="{{ route('instructions.index') }}" class="{{ request()->routeIs('instructions.index') ? 'active' : '' }}">📢 التعليمات</a>
            @endcan

            <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                🔔 الإشعارات
                @if($unreadCount > 0)
                    <span class="desktop-badge" style="margin-right: auto; margin-left: 0;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>
            <div class="mobile-separator"></div>
            <a href="{{ route('view_Overtime') }}" class="{{ request()->routeIs('view_Overtime') ? 'active' : '' }}">📊 عرض الاضافي</a>
            <a href="{{ route('view_leave') }}" class="{{ request()->routeIs('view_leave') ? 'active' : '' }}">📅 عرض الاجازات</a>
            <a href="{{ route('view_permission') }}" class="{{ request()->routeIs('view_permission') ? 'active' : '' }}">📝 عرض الاذونات</a>
            <a href="{{ route('view_check_in_out') }}" class="{{ request()->routeIs('view_check_in_out') ? 'active' : '' }}">📂 سجل الحضور</a>
            <a href="{{ route('view_penalties') }}" class="{{ request()->routeIs('view_penalties') ? 'active' : '' }}">📋 الجزاءات</a>
            <a href="{{ route('settlements.index') }}" class="{{ request()->routeIs('settlements.index') ? 'active' : '' }}">⚙️ التسويات</a>
            @can('rosters.view')
            <a href="{{ route('rosters.index') }}" class="{{ request()->routeIs('rosters.*') ? 'active' : '' }}">📅 جدول النوبتجيات والشيفتات</a>
            @endcan
            @can('penalties.create')
            <div class="mobile-separator"></div>
            <a href="{{ route('admin.penalties') }}" class="{{ request()->routeIs('admin.penalties') ? 'active' : '' }}">⚠️ إضافة جزاء</a>
            @endcan
            @can('employees.reset_password')
                <a href="{{ route('admin.reset_password') }}" class="{{ request()->routeIs('admin.reset_password') ? 'active' : '' }}">🔑 ريست باسورد</a>
            @endcan
            @can('employees.manual_entry')
                <a href="{{ route('super_admin.entry') }}" class="{{ request()->routeIs('super_admin.entry') ? 'active' : '' }}">➕ الإضافة اليدويه</a>
            @endcan
            @can('reports.full_report')
                <a href="{{ route('full_report.index') }}" class="{{ request()->routeIs('full_report.index') ? 'active' : '' }}">📄 تقارير شاملة</a>
            @endcan
            @can('employees.view_profiles')
                <a href="{{ route('employee_profiles.index') }}" class="{{ request()->routeIs('employee_profiles.*') ? 'active' : '' }}">👤 ملفات الموظفين</a>
            @endcan
            @if(Auth::user()->can('incentives.create') || Auth::user()->can('admin_notes.create') || Auth::user()->can('incentives.view') || Auth::user()->can('admin_notes.view') || Auth::user()->can('instructions.view'))
                <div class="mobile-separator"></div>
                @can('incentives.create')
                <a href="{{ route('incentives.create') }}" class="{{ request()->routeIs('incentives.create') ? 'active' : '' }}">🌟 إضافة حافز</a>
                @endcan
                @can('incentives.view')
                <a href="{{ route('incentives.index') }}" class="{{ request()->routeIs('incentives.index') ? 'active' : '' }}">📈 الحوافز</a>
                @endcan
                @can('admin_notes.create')
                <a href="{{ route('admin_notes.create') }}" class="{{ request()->routeIs('admin_notes.create') ? 'active' : '' }}">📝 إضافة ملاحظة</a>
                @endcan
                @can('admin_notes.view')
                <a href="{{ route('admin_notes.index') }}" class="{{ request()->routeIs('admin_notes.index') ? 'active' : '' }}">🗒️ ملاحظات الإدارة</a>
                @endcan
            @endif
            @if(Auth::user()->email === 'z@z.z')
                <a href="{{ route('audit_log') }}" class="{{ request()->routeIs('audit_log') ? 'active' : '' }}">📜 Audit Log</a>
            @endif
            @developer
                <div class="mobile-separator"></div>
                <a href="{{ route('developer.settings.index') }}" class="{{ request()->routeIs('developer.settings.*') ? 'active' : '' }}">⚙️ إعدادات النظام</a>
                <a href="{{ route('developer.roles.index') }}" class="{{ request()->routeIs('developer.roles.*') ? 'active' : '' }}">🛡️ الفئات والصلاحيات</a>
                <a href="{{ route('developer.shifts.index') }}" class="{{ request()->routeIs('developer.shifts.*') ? 'active' : '' }}">⏰ مجموعات الشيفتات</a>
            @enddeveloper
            <div class="mobile-separator"></div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">🚪 تسجيل خروج</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">🔑 تسجيل دخول</a>
            <span class="px-4 py-2 text-muted" style="cursor: not-allowed; opacity: 0.5;">✨ انشاء حساب (قريباً)</span>
        @endauth
    </div>
</nav>

<div class="page-content">
    @yield('content')
</div>

<footer class="footer-bar">
    <div class="footer-inner">
        Copyright © {{ date('Y') }} — Zeyad Yasser, IT Manager @ Gamma Scan Center<br>
        WhatsApp: <a href="https://wa.me/201024474059" target="_blank" rel="noopener noreferrer">wa.me/201024474059</a>
    </div>
</footer>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/flowbite.min.js') }}"></script>
<script src="{{ asset('assets/js/tom-select.complete.min.js') }}"></script>
<script>
    const btn = document.getElementById('hamburgerBtn');
    const menu = document.getElementById('mobileMenu');

    if (btn && menu) {
        btn.addEventListener('click', () => {
            btn.classList.toggle('open');
            menu.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                btn.classList.remove('open');
                menu.classList.remove('open');
            }
        });
    }

    // --- Global Loader Logic ---
    let loaderTimeout = null;
    const loader = document.getElementById('global-loader');

    function showLoader() {
        if (loader) {
            // Delay showing the loader by 300ms
            loaderTimeout = setTimeout(() => {
                loader.classList.add('show');
            }, 300);
        }
    }

    // 1. Show loader on form submissions + prevent double submit
    function unlockForms() {
        document.querySelectorAll('form[data-submitting]').forEach((f) => {
            delete f.dataset.submitting;
            f.querySelectorAll('button[type=submit], input[type=submit]').forEach((b) => b.disabled = false);
        });
    }

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (e.defaultPrevented) return;

        if (form.dataset.submitting) {
            e.preventDefault();
            return;
        }
        form.dataset.submitting = '1';

        // نعطّل الأزرار بعد ما المتصفح يجمّع بيانات الفورم
        setTimeout(() => {
            form.querySelectorAll('button[type=submit], input[type=submit]').forEach((b) => b.disabled = true);
        }, 0);

        // أمان: لو الفورم تنزيل ملف (مفيش تنقل صفحة) نفك القفل بعد 10 ثواني
        setTimeout(unlockForms, 10000);

        showLoader();
    });

    // 2. Show loader on navigation link clicks
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        
        if (link && 
            link.href && 
            !link.href.includes('#') && 
            !link.href.includes('export') && 
            !link.href.startsWith('javascript:') &&
            link.target !== '_blank' &&
            !e.ctrlKey && !e.metaKey && !e.shiftKey && e.button === 0
        ) {
            if (link.origin === window.location.origin) {
                showLoader();
            }
        }
    });

    // 3. Hide loader if page is loaded from cache (e.g. back button)
    window.addEventListener('pageshow', (event) => {
        if (loaderTimeout) {
            clearTimeout(loaderTimeout);
        }
        if (loader) {
            loader.classList.remove('show');
        }
        unlockForms();
    });

    // --- Local Push Notification Polling ---
    @auth
    setInterval(function() {
        fetch('{{ route('poll_notifications') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notif => {
                    // Send to Flutter App
                    if (window.NativeBridge) {
                        window.NativeBridge.postMessage('notify:' + notif.title + '|' + notif.message);
                    }
                });
            }
        })
        .catch(e => console.error('Polling Error:', e));
    }, 20000); // كل 20 ثانية
    @endauth
</script>
    @if(session('alert_error'))
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            alert("{{ session('alert_error') }}");
        });
    </script>
    @endif
</body>
</html>