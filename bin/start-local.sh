#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
PORT="${PORT:-8000}"
echo "Îndrumar pornește la http://127.0.0.1:${PORT}"
exec php -S "127.0.0.1:${PORT}" -t public public/router.php
