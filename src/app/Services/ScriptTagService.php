<?php

namespace App\Services;

use Closure;
use App\Helpers\ShopifyApi;

class ScriptTagService
{
	private $domain;
	private $secretKey;
	
	public function checkScriptTags($params)
    {
    	$this->domain = $params['shop'];
		$this->secretKey = $params['token'];
        $api = new ShopifyApi();
	    $api->setApiKey(env('API_KEY'));
	    $api->setSession($this->domain, $this->secretKey);
        $respData = $api->rest('GET', '/admin/api/themes.json');
        if ($respData->status) {
            $data = $respData->body;
            foreach ($data->themes as $theme) {
                if (isset($theme->role) && $theme->role === 'main') {
                    $filename = 'layout/theme.liquid';
                    $asset = $this->getContentAsset($api, $theme->id, $filename);
                    if ($asset->status && $asset->body) {
                        $content = $asset->body->asset->value;
                        if (empty(strstr($content, "<span id='imhere'></span>"))) {
                            $content = str_replace("</head>", "<span id='imhere'>haha</span>\n</head>", $content);

                            $data = ['asset' => [
                                'key' => $filename,
                                'value' => $content
                            ]];

                            $url = sprintf('/admin/api/themes/%s/assets.json', $theme->id);
                            $api->rest('PUT', $url, $data);
                        }
                    }
                }
            }
        }
    }

    protected function getContentAsset($callable, $themeId, $key)
    {
        $url = sprintf('/admin/api/themes/%s/assets.json', $themeId);
        $data = ['asset[key]' => $key, 'theme_id' => $themeId];
        $resData = $callable->rest('GET', $url, $data);

        if ($resData->status) {
            return $resData;
        } 

        return null;
    }
}