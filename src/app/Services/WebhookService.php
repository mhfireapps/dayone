<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Jobs\RegisterUninstallShopifyWebhook;

class WebhookService
{
	public function uninstall(Request $request)
	{
		$shop_url = $request->shop ?? null;
		$shop = app('AuthRepository')->deleteStores($shop_url);
	}

	public function updateTheme(Request $request, array $params = null)
	{
		$shopDomain = $request->header('X-Shopify-Shop-Domain');
		dispatch(new RegisterUninstallShopifyWebhook(
			$shopDomain,
			$params['access_token']
		)->onQueue('default');
	}

	public function orderCreated(Request $request)
	{
		// To do something
	}
}