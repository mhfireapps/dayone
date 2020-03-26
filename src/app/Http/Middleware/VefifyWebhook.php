<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VefifyWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $hmac = request()->header('x-shopify-hmac-sha256') ?: '';
        $shop = request()->header('x-shopify-shop-domain');
        $data = request()->getContent();

        // From https://help.shopify.com/api/getting-started/webhooks#verify-webhook
        $hmacLocal = base64_encode(hash_hmac('sha256', $data, env('SHARED_SECRET'), true));
        if (!hash_equals($hmac, $hmacLocal) || empty($shop)) {
            abort(401, 'Invalid webhook signature');
        }

        return $next($request);
    }
}
