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

        /* =========================================================
           --- Creative Header & Slide-Out Side Panel Styles ---
           ========================================================= */
        .creative-header {
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            color: #fff;
            padding: 0 1.25rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 64px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            width: 100%;
        }

        .header-inner {
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            gap: 0.85rem;
        }

        .header-start, .header-end, .header-center {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Menu Trigger Button */
        .menu-toggle-btn {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.18), rgba(30, 41, 59, 0.6));
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #fff;
            padding: 7px 14px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: inherit;
        }

        .menu-toggle-btn:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-color: #3b82f6;
            box-shadow: 0 0 18px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
        }

        .menu-toggle-btn .hamburger-bars {
            display: flex;
            flex-direction: column;
            gap: 3.5px;
            width: 16px;
        }

        .menu-toggle-btn .hamburger-bars span {
            display: block;
            height: 2px;
            background: #fff;
            border-radius: 2px;
            transition: all 0.25s ease;
        }

        .menu-toggle-btn .hamburger-bars span:nth-child(1) { width: 100%; }
        .menu-toggle-btn .hamburger-bars span:nth-child(2) { width: 65%; }
        .menu-toggle-btn .hamburger-bars span:nth-child(3) { width: 90%; }

        .menu-toggle-btn:hover .hamburger-bars span {
            width: 100%;
        }

        /* Brand Logo */
        .header-logo {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-shrink: 0;
            transition: opacity 0.2s ease;
        }

        .header-logo:hover {
            opacity: 0.95;
            color: #fff;
        }

        .header-logo-img {
            height: 38px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));
            transition: transform 0.25s ease;
        }

        .header-logo:hover .header-logo-img {
            transform: scale(1.05);
        }

        .header-logo span {
            background: #2563eb;
            color: #fff;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 800;
        }

        /* Active Page Pill */
        .header-page-tag {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .header-page-tag .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
        }

        /* Desktop Shortcuts */
        .header-shortcuts {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .shortcut-chip {
            color: #94a3b8;
            padding: 6px 11px;
            border-radius: 9px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: transparent;
            white-space: nowrap;
        }

        .shortcut-chip:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }

        .shortcut-chip.active {
            color: #60a5fa;
            background: rgba(59, 130, 246, 0.14);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Action Buttons on End */
        .header-icon-btn {
            position: relative;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .header-icon-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            transform: translateY(-1px);
        }

        .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background: #ef4444;
            color: #fff;
            font-size: 0.65rem;
            font-weight: bold;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            border: 2px solid #0f172a;
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
        }

        .header-user-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 4px 10px 4px 6px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .header-user-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.2);
        }

        .header-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
        }

        .header-user-info {
            display: flex;
            flex-direction: column;
            text-align: right;
            line-height: 1.2;
        }

        .header-user-name {
            font-size: 0.84rem;
            font-weight: 700;
            color: #f1f5f9;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .header-user-role {
            font-size: 0.68rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .btn-header-logout {
            padding: 6px 12px;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-header-logout:hover {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }

        /* =========================================================
           --- Slide-Out Side Panel Styles ---
           ========================================================= */
        .sidepanel-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.3s;
        }

        .sidepanel-backdrop.active {
            opacity: 1;
            visibility: visible;
        }

        .sidepanel-container {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 375px;
            max-width: 92vw;
            background: linear-gradient(180deg, #0b1120 0%, #0f172a 40%, #172554 100%);
            border-left: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: -20px 0 60px rgba(0, 0, 0, 0.7);
            z-index: 2001;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .sidepanel-container.open {
            transform: translateX(0);
        }

        /* Side Panel Header */
        .sidepanel-header {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.75);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
        }

        .sidepanel-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidepanel-title {
            font-size: 1rem;
            font-weight: 800;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidepanel-close-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1rem;
        }

        .sidepanel-close-btn:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
            transform: rotate(90deg);
        }

        /* User Profile Card inside Panel */
        .sidepanel-user-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .sidepanel-user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }

        .sidepanel-user-meta {
            flex: 1;
            min-width: 0;
        }

        .sidepanel-user-meta h5 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidepanel-user-meta span {
            font-size: 0.76rem;
            color: #94a3b8;
            display: block;
            margin-top: 2px;
        }

        .sidepanel-my-profile-link {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .sidepanel-my-profile-link:hover {
            background: #2563eb;
            color: #fff;
        }

        /* Search Input */
        .sidepanel-search-box {
            position: relative;
        }

        .sidepanel-search-input {
            width: 100%;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 8px 36px 8px 12px;
            color: #fff;
            font-size: 0.85rem;
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .sidepanel-search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.3);
            background: rgba(15, 23, 42, 0.95);
        }

        .sidepanel-search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 0.85rem;
            pointer-events: none;
        }

        /* Side Panel Body (Scrollable) */
        .sidepanel-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 1rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .sidepanel-body::-webkit-scrollbar {
            width: 5px;
        }

        .sidepanel-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        .sidepanel-body::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Categorized Groups */
        .nav-category {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .nav-category-title {
            font-size: 0.73rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 0.5rem 0.2rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sidepanel-nav-link {
            color: #cbd5e1;
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border-right: 3px solid transparent;
        }

        .sidepanel-nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(-3px);
            border-right-color: #3b82f6;
        }

        .sidepanel-nav-link.active {
            color: #60a5fa;
            background: linear-gradient(270deg, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0.05));
            border-right-color: #3b82f6;
            font-weight: 700;
        }

        .sidepanel-link-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidepanel-link-icon {
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
        }

        .sidepanel-link-badge {
            background: #ef4444;
            color: #fff;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 1px 7px;
            border-radius: 10px;
        }

        /* Side Panel Footer */
        .sidepanel-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(11, 17, 32, 0.85);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .sidepanel-logout-btn {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            font-family: inherit;
            width: 100%;
            justify-content: center;
        }

        .sidepanel-logout-btn:hover {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.35);
        }

        .sidepanel-empty-search {
            text-align: center;
            padding: 2rem 1rem;
            color: #64748b;
            font-size: 0.88rem;
            display: none;
        }

        @media (max-width: 992px) {
            .header-shortcuts { display: none; }
        }

        @media (max-width: 640px) {
            .header-user-info { display: none; }
            .header-page-tag { display: none; }
            .creative-header { padding: 0 0.85rem; }
            .header-logo span { display: none; }
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

        @media (max-width: 576px) {
            .page-content {
                padding: 10px;
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
        request()->routeIs('rosters.*') => 'جدول الشيفتات',
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

<header class="creative-header">
    <div class="header-inner">
        {{-- Right Side (Start RTL) --}}
        <div class="header-start">
            @auth
            <button class="menu-toggle-btn" id="sidePanelToggle" type="button" aria-label="فتح القائمة">
                <div class="hamburger-bars">
                    <span></span><span></span><span></span>
                </div>
                <span>القائمة</span>
            </button>
            @endauth

            <a href="/" class="header-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="header-logo-img">
                <span>HR</span> System
            </a>

            <div class="header-page-tag">
                <span class="live-dot"></span>
                <span>{{ $currentPageLabel }}</span>
            </div>
        </div>

        {{-- Center Shortcuts (Desktop) --}}
        @auth
        <div class="header-center header-shortcuts">
            <a href="{{ route('leave') }}" class="shortcut-chip {{ request()->routeIs('leave') ? 'active' : '' }}">
                <span>🏝️</span><span>إجازات</span>
            </a>
            <a href="{{ route('permission') }}" class="shortcut-chip {{ request()->routeIs('permission') ? 'active' : '' }}">
                <span>📋</span><span>أذونات</span>
            </a>
            <a href="{{ route('Overtime') }}" class="shortcut-chip {{ request()->routeIs('Overtime') ? 'active' : '' }}">
                <span>⏱️</span><span>إضافي</span>
            </a>
            @can('rosters.view')
            <a href="{{ route('rosters.index') }}" class="shortcut-chip {{ request()->routeIs('rosters.*') ? 'active' : '' }}">
                <span>📅</span><span>الشيفتات</span>
            </a>
            @endcan
        </div>
        @endauth

        {{-- Left Side (End RTL) --}}
        <div class="header-end">
            @auth
            {{-- Notifications Bell --}}
            <a href="{{ route('notifications.index') }}" class="header-icon-btn" title="الإشعارات" aria-label="الإشعارات">
                🔔
                @if($unreadCount > 0)
                    <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>

            {{-- User Pill (Click to open Side Panel) --}}
            <button type="button" class="header-user-btn" id="headerUserBtn" title="ملفك الشخصي والقائمة">
                <div class="header-avatar">
                    {{ mb_substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="header-user-info">
                    <span class="header-user-name">{{ Auth::user()->name }}</span>
                    <span class="header-user-role">{{ Auth::user()->job_title ?? Auth::user()->roleDisplayName() }}</span>
                </div>
            </button>

            {{-- Quick Logout --}}
            <form action="{{ route('logout') }}" method="POST" class="d-none d-sm-inline mb-0">
                @csrf
                <button type="submit" class="btn-header-logout" title="تسجيل الخروج">
                    <span>🚪</span>
                    <span>خروج</span>
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm fw-bold px-3 py-1.5" style="border-radius: 10px;">
                🔑 تسجيل دخول
            </a>
            @endauth
        </div>
    </div>
</header>

@auth
{{-- Backdrop Overlay --}}
<div class="sidepanel-backdrop" id="sidePanelBackdrop"></div>

{{-- Slide-Out Side Panel --}}
<aside class="sidepanel-container" id="sidePanel" aria-label="القائمة الجانبية">
    {{-- Panel Header --}}
    <div class="sidepanel-header">
        <div class="sidepanel-header-top">
            <div class="sidepanel-title">
                <span>🎛️</span>
                <span>لوحة التحكم والتنقل</span>
            </div>
            <button class="sidepanel-close-btn" id="sidePanelClose" type="button" title="إغلاق (Esc)" aria-label="إغلاق">
                ✕
            </button>
        </div>

        {{-- User Identity Card --}}
        <div class="sidepanel-user-card">
            <div class="sidepanel-user-avatar">
                {{ mb_substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="sidepanel-user-meta">
                <h5>{{ Auth::user()->name }}</h5>
                <span>{{ Auth::user()->job_title ?? Auth::user()->roleDisplayName() }}</span>
            </div>
            <a href="{{ route('my_profile') }}" class="sidepanel-my-profile-link" title="عرض بروفايلي">
                👤 بروفايلي
            </a>
        </div>

        {{-- Real-time Quick Search Bar --}}
        <div class="sidepanel-search-box">
            <span class="sidepanel-search-icon">🔍</span>
            <input type="text" id="sidepanelSearch" class="sidepanel-search-input" placeholder="ابحث في أقسام القائمة..." autocomplete="off">
        </div>
    </div>

    {{-- Panel Body (Categorized Menu Links) --}}
    <div class="sidepanel-body" id="sidepanelNav">
        {{-- Section 1: Daily Requests --}}
        <div class="nav-category" data-category="requests">
            <div class="nav-category-title">
                <span>🚀</span>
                <span>الطلبات والإجراءات اليومية</span>
            </div>
            <a href="{{ route('leave') }}" class="sidepanel-nav-link {{ request()->routeIs('leave') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">🏖️</span>
                    <span>طلب إجازة</span>
                </div>
            </a>
            <a href="{{ route('permission') }}" class="sidepanel-nav-link {{ request()->routeIs('permission') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📋</span>
                    <span>طلب إذن</span>
                </div>
            </a>
            <a href="{{ route('Overtime') }}" class="sidepanel-nav-link {{ request()->routeIs('Overtime') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⏱️</span>
                    <span>طلب إضافي</span>
                </div>
            </a>
            <a href="{{ route('check_in_out') }}" class="sidepanel-nav-link {{ request()->routeIs('check_in_out') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⏳</span>
                    <span>حضور وانصراف</span>
                </div>
            </a>
            <a href="{{ route('settlements.create') }}" class="sidepanel-nav-link {{ request()->routeIs('settlements.create') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⚙️</span>
                    <span>طلب تسوية</span>
                </div>
            </a>
            @can('instructions.view')
            <a href="{{ route('instructions.index') }}" class="sidepanel-nav-link {{ request()->routeIs('instructions.index') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📢</span>
                    <span>التعليمات والتنبيهات</span>
                </div>
            </a>
            @endcan
            <a href="{{ route('notifications.index') }}" class="sidepanel-nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">🔔</span>
                    <span>الإشعارات</span>
                </div>
                @if($unreadCount > 0)
                    <span class="sidepanel-link-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>
        </div>

        {{-- Section 2: Records & Shift Rosters --}}
        <div class="nav-category" data-category="records">
            <div class="nav-category-title">
                <span>📊</span>
                <span>متابعة السجلات والجداول</span>
            </div>
            @can('rosters.view')
            <a href="{{ route('rosters.index') }}" class="sidepanel-nav-link {{ request()->routeIs('rosters.*') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📅</span>
                    <span>جدول الشيفتات</span>
                </div>
            </a>
            @endcan
            <a href="{{ route('view_Overtime') }}" class="sidepanel-nav-link {{ request()->routeIs('view_Overtime') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📈</span>
                    <span>سجل الإضافي</span>
                </div>
            </a>
            <a href="{{ route('view_leave') }}" class="sidepanel-nav-link {{ request()->routeIs('view_leave') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">🏖️</span>
                    <span>سجل الإجازات</span>
                </div>
            </a>
            <a href="{{ route('view_permission') }}" class="sidepanel-nav-link {{ request()->routeIs('view_permission') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📝</span>
                    <span>سجل الأذونات</span>
                </div>
            </a>
            <a href="{{ route('view_check_in_out') }}" class="sidepanel-nav-link {{ request()->routeIs('view_check_in_out') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📂</span>
                    <span>سجل الحضور والانصراف</span>
                </div>
            </a>
            <a href="{{ route('view_penalties') }}" class="sidepanel-nav-link {{ request()->routeIs('view_penalties') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⚠️</span>
                    <span>سجل الجزاءات</span>
                </div>
            </a>
            <a href="{{ route('settlements.index') }}" class="sidepanel-nav-link {{ request()->routeIs('settlements.index') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⚙️</span>
                    <span>سجل التسويات</span>
                </div>
            </a>
        </div>

        {{-- Section 3: Management & HR Tools --}}
        @if(in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
        <div class="nav-category" data-category="admin">
            <div class="nav-category-title">
                <span>🛠️</span>
                <span>أدوات الإدارة والتقارير</span>
            </div>
            @can('employees.view_profiles')
            <a href="{{ route('employee_profiles.index') }}" class="sidepanel-nav-link {{ request()->routeIs('employee_profiles.*') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">👤</span>
                    <span>ملفات الموظفين</span>
                </div>
            </a>
            @endcan
            @can('reports.full_report')
            <a href="{{ route('full_report.index') }}" class="sidepanel-nav-link {{ request()->routeIs('full_report.index') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📄</span>
                    <span>التقرير الشامل</span>
                </div>
            </a>
            @endcan
            @can('employees.manual_entry')
            <a href="{{ route('super_admin.entry') }}" class="sidepanel-nav-link {{ request()->routeIs('super_admin.entry') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">➕</span>
                    <span>الإضافة اليدوية</span>
                </div>
            </a>
            @endcan
            @can('employees.reset_password')
            <a href="{{ route('admin.reset_password') }}" class="sidepanel-nav-link {{ request()->routeIs('admin.reset_password') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">🔑</span>
                    <span>استعادة كلمة السر</span>
                </div>
            </a>
            @endcan
            @can('penalties.create')
            <a href="{{ route('admin.penalties') }}" class="sidepanel-nav-link {{ request()->routeIs('admin.penalties') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⚠️</span>
                    <span>إضافة جزاء</span>
                </div>
            </a>
            @endcan
            @can('incentives.create')
            <a href="{{ route('incentives.create') }}" class="sidepanel-nav-link {{ request()->routeIs('incentives.create') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">🌟</span>
                    <span>إضافة حافز</span>
                </div>
            </a>
            @endcan
            @can('incentives.view')
            <a href="{{ route('incentives.index') }}" class="sidepanel-nav-link {{ request()->routeIs('incentives.index') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📈</span>
                    <span>عرض الحوافز</span>
                </div>
            </a>
            @endcan
            @can('admin_notes.create')
            <a href="{{ route('admin_notes.create') }}" class="sidepanel-nav-link {{ request()->routeIs('admin_notes.create') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📝</span>
                    <span>إضافة ملاحظة</span>
                </div>
            </a>
            @endcan
            @can('admin_notes.view')
            <a href="{{ route('admin_notes.index') }}" class="sidepanel-nav-link {{ request()->routeIs('admin_notes.index') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">🗒️</span>
                    <span>ملاحظات الإدارة</span>
                </div>
            </a>
            @endcan
        </div>
        @endif

        {{-- Section 4: System & Developer --}}
        @if(Auth::user()->email === 'z@z.z' || Auth::user()->email === 'mtaha@gama.com' || Auth::user()->role === 'developer')
        <div class="nav-category" data-category="developer">
            <div class="nav-category-title">
                <span>🛡️</span>
                <span>أمان وإعدادات النظام</span>
            </div>
            @if(Auth::user()->email === 'z@z.z' || Auth::user()->email === 'mtaha@gama.com')
            <a href="{{ route('audit_log') }}" class="sidepanel-nav-link {{ request()->routeIs('audit_log') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">📜</span>
                    <span>سجل العمليات (Audit Log)</span>
                </div>
            </a>
            @endif
            @developer
            <a href="{{ route('developer.settings.index') }}" class="sidepanel-nav-link {{ request()->routeIs('developer.settings.*') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⚙️</span>
                    <span>إعدادات النظام العامة</span>
                </div>
            </a>
            <a href="{{ route('developer.roles.index') }}" class="sidepanel-nav-link {{ request()->routeIs('developer.roles.*') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">🛡️</span>
                    <span>الفئات والصلاحيات</span>
                </div>
            </a>
            <a href="{{ route('developer.shifts.index') }}" class="sidepanel-nav-link {{ request()->routeIs('developer.shifts.*') ? 'active' : '' }}">
                <div class="sidepanel-link-left">
                    <span class="sidepanel-link-icon">⏰</span>
                    <span>مجموعات الشيفتات</span>
                </div>
            </a>
            @enddeveloper
        </div>
        @endif

        {{-- No Search Results Message --}}
        <div class="sidepanel-empty-search" id="sidepanelEmptySearch">
            <span>🔍 لم يتم العثور على أي رابط مطابق لبحثك</span>
        </div>
    </div>

    {{-- Panel Footer --}}
    <div class="sidepanel-footer">
        <form action="{{ route('logout') }}" method="POST" class="w-100 mb-0">
            @csrf
            <button type="submit" class="sidepanel-logout-btn">
                <span>🚪</span>
                <span>تسجيل خروج من النظام</span>
            </button>
        </form>
    </div>
</aside>
@endauth

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
    // --- Creative Slide-Out Side Panel Controller ---
    const sidePanelToggle = document.getElementById('sidePanelToggle');
    const headerUserBtn   = document.getElementById('headerUserBtn');
    const sidePanelClose  = document.getElementById('sidePanelClose');
    const sidePanel       = document.getElementById('sidePanel');
    const sidePanelBackdrop = document.getElementById('sidePanelBackdrop');
    const sidepanelSearch = document.getElementById('sidepanelSearch');
    const emptySearchMsg  = document.getElementById('sidepanelEmptySearch');

    function openSidePanel() {
        if (sidePanel && sidePanelBackdrop) {
            sidePanelBackdrop.classList.add('active');
            sidePanel.classList.add('open');
            document.body.style.overflow = 'hidden';
            if (sidepanelSearch) {
                setTimeout(() => sidepanelSearch.focus(), 300);
            }
        }
    }

    function closeSidePanel() {
        if (sidePanel && sidePanelBackdrop) {
            sidePanel.classList.remove('open');
            sidePanelBackdrop.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (sidePanelToggle) sidePanelToggle.addEventListener('click', openSidePanel);
    if (headerUserBtn) headerUserBtn.addEventListener('click', openSidePanel);
    if (sidePanelClose) sidePanelClose.addEventListener('click', closeSidePanel);
    if (sidePanelBackdrop) sidePanelBackdrop.addEventListener('click', closeSidePanel);

    // Keyboard Shortcuts: Esc to close, Ctrl+K / Cmd+K to open
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidePanel();
        } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (sidePanel && sidePanel.classList.contains('open')) {
                closeSidePanel();
            } else {
                openSidePanel();
            }
        }
    });

    // Real-Time Menu Search inside Side Panel
    if (sidepanelSearch) {
        sidepanelSearch.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const categories = document.querySelectorAll('.nav-category');
            let totalVisible = 0;

            categories.forEach(cat => {
                const links = cat.querySelectorAll('.sidepanel-nav-link');
                let catVisible = 0;

                links.forEach(link => {
                    const text = link.innerText.toLowerCase();
                    if (!query || text.includes(query)) {
                        link.style.display = 'flex';
                        catVisible++;
                        totalVisible++;
                    } else {
                        link.style.display = 'none';
                    }
                });

                if (catVisible > 0) {
                    cat.style.display = 'flex';
                } else {
                    cat.style.display = 'none';
                }
            });

            if (emptySearchMsg) {
                emptySearchMsg.style.display = (totalVisible === 0 && query) ? 'block' : 'none';
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

    function hideLoader() {
        if (loaderTimeout) {
            clearTimeout(loaderTimeout);
        }
        if (loader) {
            loader.classList.remove('show');
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

        // أمان: لو الفورم تنزيل ملف (مفيش تنقل صفحة) نفك القفل بعد 8 ثواني ونخفي اللودر
        setTimeout(() => {
            unlockForms();
            hideLoader();
        }, 8000);

        showLoader();
    });

    // 2. Show loader on navigation link clicks (تجاهل روابط التنزيل والتصدير)
    document.addEventListener('click', (e) => {
        if (e.defaultPrevented) return;

        const link = e.target.closest('a');
        if (!link || !link.href) return;

        const isDownload = link.hasAttribute('download') || 
                           link.href.includes('download') || 
                           link.href.includes('export') || 
                           link.href.includes('template') || 
                           /\.(xlsx|xls|csv|pdf|zip)$/i.test(link.href) ||
                           link.classList.contains('no-loader');

        if (isDownload) {
            hideLoader();
            return;
        }

        if (
            !link.href.includes('#') && 
            !link.href.startsWith('javascript:') &&
            link.target !== '_blank' &&
            !link.hasAttribute('data-bs-toggle') &&
            !e.ctrlKey && !e.metaKey && !e.shiftKey && e.button === 0
        ) {
            if (link.origin === window.location.origin) {
                showLoader();
                // أمان: لو الصفحة لم تتنقل بعد 8 ثوانٍ نخفي اللودر تلقائياً
                setTimeout(hideLoader, 8000);
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
        @if(request()->routeIs('employee_profiles.*'))
        if (event.persisted) {
            window.location.reload();
        }
        @endif
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