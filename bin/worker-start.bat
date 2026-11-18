@echo off
chcp 65001 >nul
title Resaname - AI Worker
cd /d "%~dp0.."
echo Starting AI worker. Each line = one processed document.
echo When the queue is empty the worker stops by itself.
"C:\Users\MamaDDelta\php\php.exe" bin\worker.php
pause
