#!/usr/bin/env bash

# ==============================================================================
# Laravel Staging Server Deployment Script
# ==============================================================================
# Usage:
#   1. Run locally from Git Bash to deploy remotely:
#        ./deploy-staging.sh
#   2. Or run directly on your staging server inside the project folder:
#        ./deploy-staging.sh --local
# ==============================================================================

set -e # Exit immediately if a command exits with a non-zero status

# --- CONFIGURATION (Update these with your staging server details) ---
STAGING_USER="forge"                          # SSH username (e.g. ubuntu, forge, root)
STAGING_HOST="staging.yourdomain.com"         # Staging server IP or domain
STAGING_PORT="22"                             # SSH port
STAGING_PATH="/var/www/custom/public_html"    # Remote project directory
BRANCH="staging"                              # Git branch to deploy (e.g. staging or main)
PHP_BIN="php"                                 # PHP binary (e.g. php, php8.4)
COMPOSER_BIN="composer"                       # Composer binary
# ---------------------------------------------------------------------

# Color codes for clean output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running locally or directly on the remote server
if [ "$1" == "--local" ]; then
    echo -e "${BLUE}==> [1/6] Running deployment tasks locally on server...${NC}"

    echo -e "${YELLOW}==> Putting application into maintenance mode...${NC}"
    $PHP_BIN artisan down || true

    echo -e "${BLUE}==> [2/6] Pulling latest code from branch '$BRANCH'...${NC}"
    git fetch origin $BRANCH
    git reset --hard origin/$BRANCH

    echo -e "${BLUE}==> [3/6] Installing Composer dependencies...${NC}"
    $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    echo -e "${BLUE}==> [4/6] Running database migrations...${NC}"
    $PHP_BIN artisan migrate --force

    echo -e "${BLUE}==> [5/6] Building frontend assets (Vite)...${NC}"
    if [ -f "package.json" ]; then
        if command -v npm &> /dev/null; then
            npm ci --prefer-offline --no-audit || npm install
            npm run build
        else
            echo -e "${YELLOW}Warning: npm not found, skipping frontend build.${NC}"
        fi
    fi

    echo -e "${BLUE}==> [6/6] Clearing and caching configuration, routes, and views...${NC}"
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache

    echo -e "${YELLOW}==> Bringing application out of maintenance mode...${NC}"
    $PHP_BIN artisan up

    echo -e "${GREEN}✓ Staging deployment finished successfully!${NC}"
    exit 0
fi

# ==============================================================================
# LOCAL WORKFLOW: Commit, Push, and Trigger Remote Deployment via SSH
# ==============================================================================

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}        STAGING SERVER GIT DEPLOYMENT              ${NC}"
echo -e "${BLUE}====================================================${NC}"

# 1. Check for uncommitted changes
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}==> Uncommitted local changes detected.${NC}"
    read -p "Do you want to commit and push all changes? (y/n): " CONFIRM_COMMIT
    if [ "$CONFIRM_COMMIT" = "y" ] || [ "$CONFIRM_COMMIT" = "Y" ]; then
        read -p "Enter commit message: " COMMIT_MSG
        if [ -z "$COMMIT_MSG" ]; then
            COMMIT_MSG="Deploy updates to staging: $(date +'%Y-%m-%d %H:%M:%S')"
        fi
        git add .
        git commit -m "$COMMIT_MSG"
    else
        echo -e "${RED}Aborting: Please commit or stash changes before deploying.${NC}"
        exit 1
    fi
fi

# 2. Push to remote git repository
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo -e "${BLUE}==> Pushing '$CURRENT_BRANCH' branch to origin...${NC}"
git push origin "$CURRENT_BRANCH"

# If current branch differs from staging branch, ask to push to staging
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
    echo -e "${YELLOW}Notice: Current local branch is '$CURRENT_BRANCH', but staging deploys '$BRANCH'.${NC}"
    read -p "Push local '$CURRENT_BRANCH' directly to remote '$BRANCH'? (y/n): " PUSH_STAGING
    if [ "$PUSH_STAGING" = "y" ] || [ "$PUSH_STAGING" = "Y" ]; then
        git push origin "$CURRENT_BRANCH:$BRANCH"
    fi
fi

# 3. Trigger remote SSH deployment commands
echo -e "${BLUE}==> Connecting to staging server ($STAGING_USER@$STAGING_HOST)...${NC}"

ssh -p "$STAGING_PORT" "$STAGING_USER@$STAGING_HOST" << EOF
    set -e
    cd "$STAGING_PATH"

    echo "==> Putting application into maintenance mode..."
    $PHP_BIN artisan down || true

    echo "==> Pulling latest changes from branch '$BRANCH'..."
    git fetch origin $BRANCH
    git reset --hard origin/$BRANCH

    echo "==> Installing PHP dependencies..."
    $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    echo "==> Running database migrations..."
    $PHP_BIN artisan migrate --force

    echo "==> Compiling frontend assets..."
    if [ -f "package.json" ]; then
        npm ci --prefer-offline --no-audit || npm install
        npm run build
    fi

    echo "==> Optimizing caches..."
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache

    echo "==> Bringing application live..."
    $PHP_BIN artisan up

    echo "✓ Deployment to Staging Complete!"
EOF

echo -e "${GREEN}====================================================${NC}"
echo -e "${GREEN}✓ Successfully deployed to staging server!         ${NC}"
echo -e "${GREEN}====================================================${NC}"
