@echo off
cd /d "%~dp0"
call composer test -- %*
exit /b %errorlevel%
