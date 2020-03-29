<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyWebhook
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
        $hmac = $request->header('X-Shopify-Hmac-Sha256') ?: '';
        $shop = $request->header('X-Shopify-Shop-Domain');
        $data = $request->getContent();
        $hmacLocal = base64_encode(hash_hmac('sha256', $data, env('SHARED_SECRET'), true));
        if (!hash_equals($hmac, $hmacLocal) || empty($shop)) {
            Log::info('Invalid webhook signature: ' . $shop);
            //abort(401, 'Invalid webhook signature');
        }

        return $next($request);
    }
}
