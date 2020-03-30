<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Jobs\DetectScriptTag;
use App\Repositories\BaseRepository;

class WebhookService
{
	public function uninstall(Request $request)
	{
		$shop_url = $request->shop ?? null;
		$shop = app(BaseRepository::AUTH)->deleteStores($shop_url);
	}

	public function updateTheme(Request $request, array $params = null)
	{
		$shopDomain = $request->header('X-Shopify-Shop-Domain');

		dispatch(new DetectScriptTag([
			'shop' => $shopDomain,
			'token' => $params['access_token']
		]))->onQueue('default');
	}

	public function orderCreated(Request $request)
	{
		//To do Something
		$orderService = app(BaseRepository::ORDER)->orderCreated($request);
	}

	public function updateShop(Request $request)
	{
		//To do Something
		$shopService = app(BaseRepository::SHOP)->saveData($request);
	}

	public function customerCreate(Request $request)
	{
		//To do Something
		$customerService = app(BaseRepository::CUSTOMER)->saveData($request);
	}

	public function customerDelete(Request $request)
	{
		//To do Something
		$customerService = app(BaseRepository::CUSTOMER)->saveData($request);
	}
}