<?php
// Quick SMTP smoke test: php artisan tinker deploy/mail-test.php
Illuminate\Support\Facades\Mail::raw(
    'AutoSpa Pro mail test from expresscarwash.co.ke — SMTP is working.',
    function ($message) {
        $message->to('info@designekta.com')->subject('AutoSpa Pro mail test');
    }
);

echo "Mail sent without exceptions\n";
