<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // 必要ならここに $except を書ける
    // protected $except = ['stripe/webhook', 'api/stripe/webhook'];
}
