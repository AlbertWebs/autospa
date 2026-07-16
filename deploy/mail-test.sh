#!/usr/bin/env bash
# SMTP smoke test with a hard timeout. Usage: bash mail-test.sh
set -u
cd /var/www/expresscarwash

timeout 60 php artisan tinker --execute="
Illuminate\Support\Facades\Mail::raw(
    'AutoSpa Pro mail test from expresscarwash.co.ke — SMTP is working.',
    function (\$m) { \$m->to('info@designekta.com')->subject('AutoSpa Pro mail test'); }
);
echo 'MAIL_SENT_OK';
" 2>&1

code=$?
if [ $code -eq 124 ]; then
    echo "TIMED_OUT: SMTP connection hung for 60s"
fi
exit 0
