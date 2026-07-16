#!/usr/bin/env bash
# Simulate a real login POST with cookies + CSRF token like a browser would.
set -u
JAR=$(mktemp)

TOKEN=$(curl -s -c "$JAR" https://expresscarwash.co.ke/login \
    | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')

echo "token: ${TOKEN:0:12}..."

CODE=$(curl -s -o /tmp/login-resp.html -w '%{http_code}' -b "$JAR" -c "$JAR" \
    -X POST https://expresscarwash.co.ke/login \
    -d "_token=$TOKEN" \
    -d "login_method=password" \
    -d "email=csrf-test@example.com" \
    -d "password=wrongpassword")

echo "POST /login with valid token+cookies: HTTP $CODE (422/302 = CSRF OK, 419 = broken)"
rm -f "$JAR" /tmp/login-resp.html
