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
    	
    }

    public function orders(Request $request)
    {
    	// Todo domething
    }

    public function themes(Request $request)
    {
    	// Todo domething
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
