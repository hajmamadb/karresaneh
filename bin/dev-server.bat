@echo off
chcp 65001 >nul
title Resaname - Web Server (port 8000)
cd /d "%~dp0.."
echo Starting web server... do NOT close this window while you use the dashboard.
"C:\Users\MamaDDelta\php\php.exe" -S localhost:8000 -t "%~dp0..\public"
pause
