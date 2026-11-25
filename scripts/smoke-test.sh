#!/usr/bin/env bash
# SMOKE TESTS: CHECK THE INSTALLED SITE THE WAY A VISITOR SEES IT.
# EXPECTS A SITE INSTALLED FROM CONFIG/SYNC AND FILLED WITH THE DEMO CONTENT.
# USAGE: scripts/smoke-test.sh [BASE_URL]

set -euo pipefail

BASE_URL="${1:-https://drupal-security-bulletins.ddev.site}"
FAILURES=0

# PRINTS ONE RESULT LINE AND COUNTS THE FAILURES.
check() {
  local description="$1" expected="$2" actual="$3"
  if [[ "$expected" == "$actual" ]]; then
    echo "PASS  $description"
  else
    echo "FAIL  $description (expected: $expected, got: $actual)"
    FAILURES=$((FAILURES + 1))
  fi
}

# HTTP STATUS OF A PAGE. -k: DDEV SERVES A CERTIFICATE FROM A LOCAL AUTHORITY.
status() {
  curl -sk -o /dev/null -w '%{http_code}' "$BASE_URL$1"
}

# BODY OF A PAGE.
body() {
  curl -sk "$BASE_URL$1"
}

# NUMBER OF BULLETIN CARDS IN A PAGE: THE CLASS ALONE, NOT ITS __ELEMENTS.
cards() {
  body "$1" | { grep -oE '[" ]bulletin-card[" ]' || true; } | wc -l | tr -d ' '
}

# VALUE OF A RESPONSE HEADER, EMPTY WHEN ABSENT.
header() {
  curl -skI "$BASE_URL$1" | { grep -i "^$2:" || true; } | cut -d' ' -f2- | tr -d '\r'
}

# PRINTS "yes" WHEN A PAGE CONTAINS A STRING.
contains() {
  [[ "$(body "$1")" == *"$2"* ]] && echo yes || echo no
}

echo "Smoke tests against $BASE_URL"

check "Home page responds" 200 "$(status /)"
check "Summary block is on the home page" yes "$(contains / severity-summary)"
check "Listing responds" 200 "$(status /bulletins)"
check "Listing shows the 10 demo bulletins" 10 "$(cards /bulletins)"
check "Severity filter keeps the 2 critical bulletins" 2 "$(cards '/bulletins?field_severity_value=critical')"
check "Bulletin page responds" 200 "$(status /bulletins/injection-sql-dans-la-recherche-du-portail-rh-mirabelle)"
check "RSS feed responds" 200 "$(status /bulletins/rss)"
check "Visitors cannot register" 403 "$(status /user/register)"
check "Audit trail is not public" 403 "$(status /admin/reports/audit-trail)"
check "Content-Security-Policy header is sent" yes "$([[ -n "$(header / content-security-policy)" ]] && echo yes || echo no)"
check "Clickjacking protection header is sent" SAMEORIGIN "$(header / x-frame-options)"

if (( FAILURES > 0 )); then
  echo "$FAILURES smoke test(s) failed."
  exit 1
fi
echo "All smoke tests passed."