#!/usr/bin/env bash
#
# reset-lab.sh — Reset the SessionVault lab to a clean state.
#
# This script removes the PostgreSQL data volume, recreates the database,
# runs all migrations, and re-seeds the lab data. It is the canonical way
# to return the lab to its initial state for repeatable testing.
#
# Usage:
#   ./scripts/reset-lab.sh          # vulnerable mode (default, all SM-01..SM-12)
#   ./scripts/reset-lab.sh secure    # secure mode (secure reference implementations)
#
set -euo pipefail

# Resolve the repository root (parent of the scripts/ directory).
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

MODE="${1:-vulnerable}"

echo ">>> Resetting SessionVault lab (mode: $MODE)"

# Bring everything down and remove the database volume.
docker compose down -v

# Start the stack from scratch so migrations + seeds run cleanly via the
# docker-entrypoint.sh on container boot.
docker compose up -d

# Wait for the app to finish its entrypoint migrations/seeds (max ~60s).
echo ">>> Waiting for the application to finish database migrations and seeding..."
for i in $(seq 1 60); do
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/ 2>/dev/null | grep -q "200\|302\|500"; then
        # 500 may be returned transiently during boot; wait for 200/302.
        code="$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/ 2>/dev/null || echo "")"
        if [ "$code" = "200" ] || [ "$code" = "302" ]; then
            echo ">>> Application is ready (HTTP $code)."
            break
        fi
    fi
    printf "\r>>> Waiting... (%ds)" "$i"
    sleep 1
done

echo ">>> Resetting done."
echo
echo "Lab URL:       http://localhost:8080"
echo "Login (admin): admin@example.local / LabPass123!"
echo
echo "Seeded accounts:"
echo "  Admin    admin@example.local / LabPass123!"
echo "  User     alice@example.local  / LabPass123!"
echo
echo "Burp Suite testing guidance is in SECURITY-LAB.md."
