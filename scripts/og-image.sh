#!/bin/zsh
# Renders resources/views/og-image.blade.php to public/images/og.png (1200×630) with headless Chrome.
# Needs the site running locally in Herd and up-to-date assets (npm run build).
set -euo pipefail
cd "$(dirname "$0")/.."
URL=${OG_URL:-https://gotaticket.ch.test/og-image}
CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
"$CHROME" --headless --disable-gpu --hide-scrollbars --ignore-certificate-errors --force-device-scale-factor=1 \
	--window-size=1200,630 --virtual-time-budget=5000 \
	--screenshot="$PWD/public/images/og.png" "$URL" 2>/dev/null
echo "Wrote public/images/og.png"
