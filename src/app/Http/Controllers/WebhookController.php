<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AuthRepository;
use App\Helpers\ShopifyApi;
use App\Services\WebhookService;

class WebhookController extends Controller
{
	protected $model;
    protected $apiVersion = '2020-01';

	public function __construct(AuthRepository $auth)
	{
		$this->model = $auth;
	}

    public function all(Request $request)
    {
        $bodyContent = $request->getContent();
        $shopName = isset($request->shop) ? $request->shop : 'dayoneapp.myshopify.com';
        $info = $this->model->getAuth($shopName);
        if (isset($info->access_token)) {
            $api = new ShopifyApi();
            $api->setVersion($this->apiVersion);
            $api->setApiKey(env('API_KEY'));
            $api->setApiSecret($secretKey);
            $resp = $api->withSession($shopName, $info->access_token, function() {
                $resp = $api->rest('GET', '/admin/api/webhooks.json');
                return $resp->body;
            });

            return '[Webhook: list]: ' . json_encode($resp);
        }

        return '[Webhook: list]: ' . $shopName;
    }

    public function products(Request $request)
    {
    	$bodyContent = $request->getContent();
        return '[Webhook: products]: ' . $bodyContent;
    }

    public function orders(Request $request)
    {
    	$bodyContent = $request->getContent();
        // To do something
        $service = new WebhookService();
        $service->orderCreated($request);

        return '[Webhook: orders/create]: ' . $bodyContent;
    }

    public function themeUpdated(Request $request)
    {
        $bodyContent = $request->getContent();
        $shopName = isset($request->shop) ? $request->shop : 'dayoneapp.myshopify.com';
        $info = $this->model->getAuth($shopName);
        if ( isset($info->access_token) ) {
            $service = new WebhookService();
            $service->updateTheme($request, array('access_token' => $info->access_token));
        }

        return '[Webhook: Theme/updated]: ' . $bodyContent;
    }

    public function uninstall(Request $request)
    {
        $service = new WebhookService();
        $service->uninstall($request);

    	return 'Uninstall successfully';
    }

    public function shop(Request $request)
    {
    	// Todo domething
    }
}
