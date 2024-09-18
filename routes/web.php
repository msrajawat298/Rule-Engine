<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/send-test-email', function () {
    Mail::raw('This is a test email sent to MailHog.', function ($message) {
        $message->to('help@opentext.com')
                ->subject('Test Email');
    });

    return 'Email sent!';
});