@echo off
chcp 65001 > nul
title HR System - تشغيل البيئة التجريبية (Demo Sandbox)

echo ================================================================
echo        🚀 جاري تشغيل النسخة التجريبية (HR Demo Sandbox)
echo ================================================================
echo.
echo  • قاعدة البيانات: tk_demo (بيانات وهمية مستقلة 100%%)
echo  • الرابط:         http://localhost:8088
echo  • حساب الأدمن:    admin@demo.com
echo  • كلمة المرور:    demo123456
echo.
echo ================================================================
echo.

start http://localhost:8088

php artisan serve --env=demo --port=8088

pause
