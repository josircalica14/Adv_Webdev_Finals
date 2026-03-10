@echo off
REM Integration Test Runner for Multi-User Portfolio Platform (Windows)
REM This script runs all integration, security, and performance tests

echo ==========================================
echo Multi-User Portfolio Platform Test Suite
echo ==========================================
echo.

REM Check if PHPUnit is installed
where phpunit >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: PHPUnit is not installed
    echo Please install PHPUnit: composer require --dev phpunit/phpunit
    exit /b 1
)

REM Check if test database is configured
if "%TEST_DB_HOST%"=="" (
    echo Warning: TEST_DB_HOST not set, using default 'localhost'
    set TEST_DB_HOST=localhost
)

if "%TEST_DB_NAME%"=="" (
    echo Warning: TEST_DB_NAME not set, using default 'portfolio_test'
    set TEST_DB_NAME=portfolio_test
)

echo Test Configuration:
echo   Database Host: %TEST_DB_HOST%
echo   Database Name: %TEST_DB_NAME%
echo.

set total_suites=0
set passed_suites=0
set failed_suites=0

REM Run End-to-End Integration Tests
echo Running End-to-End Integration Tests...
echo ----------------------------------------
set /a total_suites+=1
phpunit --colors=always tests/Integration/EndToEndIntegrationTest.php
if %ERRORLEVEL% EQU 0 (
    echo [PASSED] End-to-End Integration Tests
    set /a passed_suites+=1
) else (
    echo [FAILED] End-to-End Integration Tests
    set /a failed_suites+=1
)
echo.

REM Run Security Audit Tests
echo Running Security Audit Tests...
echo ----------------------------------------
set /a total_suites+=1
phpunit --colors=always tests/Security/SecurityAuditTest.php
if %ERRORLEVEL% EQU 0 (
    echo [PASSED] Security Audit Tests
    set /a passed_suites+=1
) else (
    echo [FAILED] Security Audit Tests
    set /a failed_suites+=1
)
echo.

REM Run Performance Tests
echo Running Performance Tests...
echo ----------------------------------------
set /a total_suites+=1
phpunit --colors=always tests/Performance/PerformanceTest.php
if %ERRORLEVEL% EQU 0 (
    echo [PASSED] Performance Tests
    set /a passed_suites+=1
) else (
    echo [FAILED] Performance Tests
    set /a failed_suites+=1
)
echo.

REM Print summary
echo ==========================================
echo Test Summary
echo ==========================================
echo Total Test Suites: %total_suites%
echo Passed: %passed_suites%
echo Failed: %failed_suites%
echo.

if %failed_suites% GTR 0 (
    echo Some tests failed. Please review the output above.
    exit /b 1
) else (
    echo All tests passed successfully!
    exit /b 0
)
