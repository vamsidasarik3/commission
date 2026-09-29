#!/usr/bin/env bash

# ==============================================================================
# Git Initialization Script (Git Bash / Linux / macOS)
# ==============================================================================

set -e

echo "===================================================="
echo "       INITIALIZING GIT IN PROJECT FOLDER           "
echo "===================================================="

if ! command -v git &> /dev/null; then
    echo "[ERROR] Git is not installed or not in PATH!"
    echo "Please download and install Git: https://git-scm.com/download/win"
    exit 1
fi

echo "==> [1/5] Initializing Git repository..."
git init

echo "==> [2/5] Setting default branch to main..."
git branch -M main

echo "==> [3/5] Checking Git user config..."
if [ -z "$(git config user.name)" ]; then
    read -p "Enter your name for Git commits: " GIT_NAME
    read -p "Enter your email for Git commits: " GIT_EMAIL
    git config user.name "$GIT_NAME"
    git config user.email "$GIT_EMAIL"
fi

echo ""
read -p "Enter your remote Git Repository URL (e.g., https://github.com/user/repo.git) [leave blank to skip]: " REPO_URL

if [ -n "$REPO_URL" ]; then
    echo "==> [4/5] Adding remote origin..."
    git remote remove origin 2>/dev/null || true
    git remote add origin "$REPO_URL"
fi

echo "==> [5/5] Adding files and making initial commit..."
git add .
git commit -m "Initial commit: Commission model application with dynamic rates"

echo ""
echo "===================================================="
echo "✓ Git repository successfully initialized!"
if [ -n "$REPO_URL" ]; then
    echo "To push your code to your remote staging/main repo, run:"
    echo "    git push -u origin main"
fi
echo "===================================================="
