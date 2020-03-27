<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\ShopifyApi;

class RegisterUninstallShopifyWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($domain, $token)
    {
        $this->domain = $domain;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->checkScriptTags();
    }

    protected function checkScriptTags()
    {
        $api = new ShopifyApi();
        $resp = $api->withSession($this->domain, $this->token, function() {
            $resp = $this->rest('GET', '/admin/api/themes.json');
            return $resp;
        });

        if ($resp->status) {
            $data = $resp->body;
            foreach ($data['themes'] as $theme) {
                if (isset($theme['role']) && $theme['role'] === 'main') {
                    $filename = 'layout/theme.liquid';
                    $asset = $this->getContentAsset($this->domain, $filename);
                    if ($asset->status) {
                        $content = $asset->body;

                        if (empty(strstr($content, "<span id='imhere'></span>"))) {
                            $content = str_replace("</head>", "<span id='imhere'></span>\n</head>", $content);

                            /**
                             * Update content of theme.liquid
                             */
                            $data = ['asset' => [
                                'key' => $filename,
                                'value' => $content
                            ]];

                            $url = sprintf('themes/%s/assets.json', $this->domain);
                            $resData = $api->withSession($this->domain, $this->token, function() use ($url) {
                                $resp = $this->rest('PUT', $url);
                                return $resp;
                            });
                        }
                    }
                }
            }
        }
    }

    protected function getContentAsset($themeId)
    {
        $url = sprintf('themes/%s/assets.json', $themeId);
        $data = ['asset[key]' => $key, 'theme_id' => $themeId];
        $api = new ShopifyApi();
        $resData = $api->withSession($this->domain, $this->token, function() use ($url) {
            $resp = $this->rest('GET', $url);
            return $resp;
        });

        if ($resData->status) {
            return $resData->body;
        } 

        return null;
    }
}
