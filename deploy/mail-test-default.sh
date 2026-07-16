#!/usr/bin/env bash
# Send a test using the app's default from address (no override).
set -u
cd /var/www/expresscarwash

timeout 60 php artisan tinker --execute="
try {
    Illuminate\Support\Facades\Mail::raw(
        'Express Car Wash default sender test — emails now come from hello@expresscarwash.co.ke.',
        function (\$m) {
            \$m->to('info@designekta.com')->subject('Express Car Wash mail configured');
        }
    );
    echo 'DEFAULT_FROM_OK';
} catch (Throwable \$e) {
    echo 'DEFAULT_FROM_FAILED: '.\$e->getMessage();
}
" 2>&1

exit 0
