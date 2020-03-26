<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Repositories\AuthRepository;
use App\Helpers\ShopifyApi;

class AppController extends BaseController
{
	protected $model;

	public function __construct(AuthRepository $auth)
	{
		$this->model = $auth;
	}

	/**
	 * Installing shopify apps to stores
	 * @param  Request $request
	 * @return mixed
	 */
    public function install(Request $request)
    {
    	$shopName = isset($request->shop) ? $request->shop : '';
    	if ($shopName) {
    		$scopes = 'read_script_tags,write_script_tags';
		    $url = 'https://'.$shopName.'/admin/oauth/authorize?client_id='.env('API_KEY').'&scope='.$scopes.'&redirect_uri='.env('APP_URL').'/auth';
		    return redirect($url);
    	}

	    return 'OOPS! Something went wrong';
    }

    /**
     * Generating Token
     * @param  Request $request
     * @return void
     */
    public function auth(Request $request)
    {
    	$secretKey = env('SHARED_SECRET');
    	$api = new ShopifyApi();
    	$api->setApiSecret($secretKey);
    	$valid = $api->verifyRequest($_GET);

		if ( $valid ) {
			$code = $request->get('code');
			$api->setApiKey(env('API_KEY'));

			$shopName = isset($request->shop) ? $request->shop : '';
			if ( $shopName ) {
				$api->setShop($shopName);
				$response = $api->requestAccess($code);
				if ( isset($response['access_token']) ) {
					$result = $this->model->saveAuth( array(
						'store_url'    => $shopName,
						'access_token' => $response['access_token']
					));
				} else {
					return 'OOPS! Something went wrong';
				}
			}
		}

    	return redirect('/');
    }

    /**
     * Check script tags
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function scriptsTag(Request $request)
    {
    	$secretKey = env('SHARED_SECRET');
    	$shopName = isset($request->shop) ? $request->shop : 'dayoneapp.myshopify.com';
    	$info = $this->model->getAuth($shopName);
    	if (isset($info->access_token)) {
    		$api = new ShopifyApi();
	    	$api->setVersion('2020-01');
	    	$api->setShop($shopName);
	    	$api->setApiKey(env('API_KEY'));
	    	$api->setApiSecret($secretKey);
	    	$api->setAccessToken($info->access_token);
	    	$response = $api->verifyScriptsTag('GET');
	    	return "[ScripTags]: {$response->status} " . json_encode($response->body);
    	}
    	
    	return 'hi there';
    }
}
