#!/usr/bin/env bash
#
# Oeffnet einen HTTPS-Tunnel auf den lokalen Entwicklungsserver, damit die
# Online-Ueberweisung vollstaendig getestet werden kann.
#
# Warum ueberhaupt ein Tunnel: Micropayment meldet den Zahlungsstatus
# serverseitig zurueck (Benachrichtigung) und leitet die Kundin erst danach
# zurueck in den Shop. Dieser Rueckruf kommt von einem fremden Server und
# erreicht `localhost` nicht. Ohne Tunnel bleibt die Bestellung unbezahlt und
# Micropayment antwortet mit „Die Weiterleitung wurde nicht erlaubt“.
#
# Aufruf:  ./tunnel.sh
# Beenden: Strg-C  (traegt APP_URL automatisch zurueck)
#
# Die Adresse eines Schnelltunnels wechselt bei jedem Start. Das Skript traegt
# sie in .env ein und gibt die Benachrichtigungsadresse aus, die im
# Micropayment-Control-Center hinterlegt werden muss.

set -euo pipefail

cd "$(dirname "$0")"

PORT="${PORT:-8000}"
ENV_FILE=".env"
LOG_FILE="storage/logs/cloudflared.log"

command -v cloudflared >/dev/null 2>&1 || {
    echo "cloudflared fehlt. Installieren mit:  brew install cloudflared" >&2
    exit 1
}

# Laeuft der Entwicklungsserver? Ein Tunnel ins Leere kostet nur Sucherei.
if ! curl -s -o /dev/null --max-time 3 "http://127.0.0.1:${PORT}/"; then
    echo "Auf Port ${PORT} antwortet nichts. Zuerst den Server starten:" >&2
    echo "  PHP_CLI_SERVER_WORKERS=4 php artisan serve" >&2
    exit 1
fi

# APP_URL sichern, um sie beim Beenden wiederherzustellen.
PREVIOUS_APP_URL="$(grep -E '^APP_URL=' "$ENV_FILE" | head -1 | cut -d= -f2- || true)"

set_app_url() {
    # sed -i verhaelt sich unter macOS anders als unter Linux, deshalb ueber
    # eine temporaere Datei statt in-place.
    local value="$1" tmp
    tmp="$(mktemp)"
    awk -v url="$value" '
        /^APP_URL=/ { print "APP_URL=" url; found = 1; next }
        { print }
        END { if (!found) print "APP_URL=" url }
    ' "$ENV_FILE" > "$tmp"
    mv "$tmp" "$ENV_FILE"
}

cleanup() {
    echo
    echo "Tunnel wird geschlossen."
    [ -n "${TUNNEL_PID:-}" ] && kill "$TUNNEL_PID" 2>/dev/null || true
    set_app_url "${PREVIOUS_APP_URL:-http://localhost}"
    echo "APP_URL zurueckgesetzt auf ${PREVIOUS_APP_URL:-http://localhost}"
}
trap cleanup EXIT INT TERM

: > "$LOG_FILE"
cloudflared tunnel --url "http://127.0.0.1:${PORT}" --no-autoupdate > "$LOG_FILE" 2>&1 &
TUNNEL_PID=$!

echo "Tunnel startet …"
TUNNEL_URL=""
for _ in $(seq 1 30); do
    TUNNEL_URL="$(grep -oE 'https://[a-z0-9-]+\.trycloudflare\.com' "$LOG_FILE" | head -1 || true)"
    [ -n "$TUNNEL_URL" ] && break
    sleep 1
done

if [ -z "$TUNNEL_URL" ]; then
    echo "Keine Tunnel-Adresse erhalten. Protokoll: ${LOG_FILE}" >&2
    exit 1
fi

set_app_url "$TUNNEL_URL"

cat <<INFO

  Shop            ${TUNNEL_URL}
  Benachrichtigung ${TUNNEL_URL}/payment/micropayment/notify

  Die Benachrichtigungsadresse im Micropayment-Control-Center beim Projekt
  hinterlegen (Zahlart Online-Ueberweisung). Sie aendert sich bei jedem Start
  dieses Skripts und muss dort jedes Mal neu eingetragen werden.

  Den Shop ab jetzt ueber die Tunnel-Adresse aufrufen, nicht ueber localhost:
  die Zahlungslinks sind signiert und gelten nur fuer den Host, unter dem sie
  erzeugt wurden.

  Beenden mit Strg-C.

INFO

wait "$TUNNEL_PID"
