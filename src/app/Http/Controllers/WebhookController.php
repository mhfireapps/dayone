<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AuthRepository;
use App\Helpers\ShopifyApi;

class WebhookController extends Controller
{
	protected $model;

	public function __construct(AuthRepository $auth)
	{
		$this->model = $auth;
	}

    public function products(Request $request)
    {
    	$bodyContent = $request->getContent();
        return '[Webhook: products]: ' . $bodyContent;
    }

    public function orders(Request $request)
    {
    	$bodyContent = $request->getContent();
        return '[Webhook: orders/create]: ' . $bodyContent;
    }

    public function themeUpdated(Request $request)
    {
        $bodyContent = $request->getContent();
        return '[Webhook: Theme/updated]: ' . $bodyContent;
    }

    public function uninstall(Request $request)
    {
    	$shop_name = isset($request->shop) ? $request->shop : '';
    	if ($shop_name) {
    		$this->model->deleteStores($shop_name);
    	}

    	return 'Uninstall successfully';
    }

    public function shop(Request $request)
    {
    	// Todo domething
    }
}
