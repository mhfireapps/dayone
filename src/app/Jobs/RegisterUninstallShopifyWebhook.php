<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
     * @var \App\Store
     */
    public $store;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($domain, $token, \App\Store $store)
    {
        $this->domain = $domain;
        $this->token = $token;
        $this->store = $store;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    
    }
}
