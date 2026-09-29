@echo off
REM ==============================================================================
REM Git Initialization Script for Windows (public_html)
REM ==============================================================================

echo ====================================================
echo        INITIALIZING GIT IN PROJECT FOLDER
echo ====================================================

REM Check if git command exists
where git >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Git is not installed or not in your Windows PATH!
    echo Please download and install Git for Windows from: https://git-scm.com/download/win
    echo After installing, restart your terminal and run this script again.
    pause
    exit /b 1
)

echo [1/5] Initializing empty Git repository...
git init

echo.
echo [2/5] Setting default branch to main...
git branch -M main

echo.
echo [3/5] Checking Git user configuration...
git config user.name >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    set /p GIT_NAME="Enter your name for Git commits (e.g. John Doe): "
    set /p GIT_EMAIL="Enter your email for Git commits (e.g. john@example.com): "
    git config user.name "%GIT_NAME%"
    git config user.email "%GIT_EMAIL%"
)

echo.
set /p REPO_URL="Enter your remote Git Repository URL (e.g. https://github.com/user/repo.git or git@github.com:user/repo.git): "

if not "%REPO_URL%"=="" (
    echo [4/5] Adding remote origin: %REPO_URL%
    git remote remove origin >nul 2>nul
    git remote add origin %REPO_URL%
) else (
    echo [Notice] Skipping remote origin setup. You can add it later with: git remote add origin ^<url^>
)

echo.
echo [5/5] Adding files and creating initial commit...
git add .
git status
git commit -m "Initial commit: Commission model application with dynamic rates"

echo.
if not "%REPO_URL%"=="" (
    echo ====================================================
    echo Repository initialized and committed!
    echo To push your code to your remote staging/main branch, run:
    echo     git push -u origin main
    echo ====================================================
) else (
    echo ====================================================
    echo Repository initialized locally!
    echo ====================================================
)

pause
