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
    	$shop_name = isset($request->shop) ? $request->shop : '';
    	if ($shop_name) {
    		$scopes = 'read_script_tags,write_script_tags';
		    $url = 'https://'.$shop_name.'/admin/oauth/authorize?client_id='.env('API_KEY').'&scope='.$scopes.'&redirect_uri='.env('APP_URL').'/auth';
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
    	$secret_key = env('SHARED_SECRET');
    	$api = new ShopifyApi();
    	$api->setApiSecret($secret_key);
    	$valid = $api->verifyRequest($_GET);

		if ( $valid ) {
			$code = $request->get('code');
			$api->setApiKey(env('API_KEY'));

			$shop_name = isset($request->shop) ? $request->shop : '';
			if ( $shop_name ) {
				$api->setShop($shop_name);
				$response = $api->requestAccess($code);
				if ( isset($response['access_token']) ) {
					$result = $this->model->saveAuth( array(
						'store_url'    => $shop_name,
						'access_token' => $response['access_token']
					));
				} else {
					return 'OOPS! Something went wrong';
				}
			}
		}

    	return redirect('/');
    }
}
