@echo off
setlocal enabledelayedexpansion
set PORT=8000
:search
netstat -ano | findstr ":%PORT% " >nul
if %errorlevel% equ 0 ( set /a PORT+=1 & goto :search )
set PORT=%PORT%
docker compose up -d
echo ---------------------------------------------------
echo  READY (Windows): http://localhost:%PORT%
echo ---------------------------------------------------
pause
