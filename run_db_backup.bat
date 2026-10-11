@echo off

:: Calculate 12-hour AM/PM timestamp
setlocal enabledelayedexpansion
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set YYYY=!datetime:~0,4!
set MM=!datetime:~4,2!
set DD=!datetime:~6,2!
set /a HH=1!datetime:~8,2! - 100
set MIN=!datetime:~10,2!

set AMPM=AM
if !HH! geq 12 (
    set AMPM=PM
    if !HH! gtr 12 set /a HH-=12
)
if !HH! equ 0 set HH=12
if !HH! lss 10 set HH=0!HH!

set TS=!YYYY!-!MM!-!DD!_!HH!-!MIN!_!AMPM!
endlocal & set TIMESTAMP=%TS%

:: Define paths
set BACKUP_DIR=D:\xampp\htdocs\tk\DB BK
set BACKUP_FILE=%BACKUP_DIR%\backup_tk_%TIMESTAMP%.sql
set MYSQLDUMP=C:\Program Files\MySQL\MySQL Server 26.7\bin\mysqldump.exe

:: Ensure backup directory exists
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: Read DB password from .env if present
set DB_PWD=
for /f "usebackq tokens=1,2 delims==" %%A in (`findstr /i "^DB_PASSWORD=" "%~dp0.env" 2^>nul`) do set DB_PWD=%%B

set PWD_ARG=
if defined DB_PWD set PWD_ARG=-p%DB_PWD%

:: Run backup
echo Creating backup at %TIMESTAMP%...
"%MYSQLDUMP%" -h 127.0.0.1 -u root %PWD_ARG% --single-transaction tk > "%BACKUP_FILE%"

if %ERRORLEVEL% equ 0 (
    echo Backup successful: %BACKUP_FILE%
) else (
    echo Backup failed!
)
