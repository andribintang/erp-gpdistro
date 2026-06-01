@echo off
setlocal
cd /d "%~dp0"
set "PHP=%~dp0..\..\php\php.exe"

if not exist "%PHP%" (
    echo PHP XAMPP tidak ditemukan di "%PHP%".
    pause
    exit /b 1
)

echo GPDISTRO ERP lokal berjalan di http://127.0.0.1:8000
echo Tekan Ctrl+C untuk menghentikan server.
"%PHP%" artisan view:clear
"%PHP%" artisan serve --host=127.0.0.1 --port=8000
