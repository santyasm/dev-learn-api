<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    // return view('welcome');
    dd(
        'Request is Secure?', $request->isSecure(),
        'X-Forwarded-Proto Header:', $request->header('x-forwarded-proto'),
        'All Headers:', $request->headers->all()
    );
});

Route::get('/up', function () {
    return "OK";
});