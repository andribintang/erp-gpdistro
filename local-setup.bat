@echo off
setlocal
cd /d "%~dp0"
set "PHP=%~dp0..\..\php\php.exe"

if not exist "%PHP%" (
    echo PHP XAMPP tidak ditemukan di "%PHP%".
    pause
    exit /b 1
)

echo Menyiapkan database lokal...
"%PHP%" artisan migrate --force
if errorlevel 1 goto :error

echo Mengisi akun admin dan data demo...
"%PHP%" artisan db:seed --force
if errorlevel 1 goto :error

echo Membangun asset frontend...
call npm.cmd run build
if errorlevel 1 goto :error

echo Membersihkan cache tampilan...
"%PHP%" artisan optimize:clear
if errorlevel 1 goto :error

echo.
echo Setup lokal selesai.
echo Login: admin@gpdistro.test
echo Password: change-me-now
echo Jalankan local-start.bat untuk membuka server lokal.
pause
exit /b 0

:error
echo.
echo Setup lokal gagal. Periksa pesan di atas.
pause
exit /b 1
