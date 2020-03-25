<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Respositories\AuthRespository;
use App\Helpsrs\Curl;

class AppController extends BaseController
{
	protected $model;
	public function __construct(AuthRespository $auth)
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
    	$hmac = $request->get('hmac');
    	$params = $_GET;
    	$params = isset($params['hmac']) ? unset($params['hmac']) : $params;
    	ksort($params);
    	$pc_hmac = hash_hmac('sha256', http_build_query($params), $secret_key);
		if (hash_equals($hmac, $pc_hmac)) {
			$code = $request->get('code');
			$query = array(
				'client_id' 	=> env('API_KEY'],
				'client_secret' => $secret_key,
				'code' 		    => $code
			);

			$shop_name = isset($request->shop) ? $request->shop : '';
			if ($shop_name) {
				$access_token_url = 'https://' . $shop_name . '/admin/oauth/access_token';
				$curl = new Curl();
				$curl->setMethod(2);
				$curl->setUsingJson(false);
				$response = $curl->call($access_token_url, $query);

				$response = json_decode($response, true);
				if (isset($response['access_token'])) {

				} else {
					return 'OOPS! Something went wrong';
				}
			}
		}

    	return redirect('/');
    }
}
