#!/usr/bin/env bash
# Test sending from hello@expresscarwash.co.ke through the configured SMTP.
set -u
cd /var/www/expresscarwash

timeout 60 php artisan tinker --execute="
try {
    Illuminate\Support\Facades\Mail::raw(
        'Test from hello@expresscarwash.co.ke via SES.',
        function (\$m) {
            \$m->from('hello@expresscarwash.co.ke', 'Express Car Wash')
                ->to('info@designekta.com')
                ->subject('Express Car Wash sender test');
        }
    );
    echo 'FROM_TEST_OK';
} catch (Throwable \$e) {
    echo 'FROM_TEST_FAILED: '.\$e->getMessage();
}
" 2>&1

exit 0
