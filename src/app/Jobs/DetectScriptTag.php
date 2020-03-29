<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DetectScriptTag implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public $shop;

    /**
     * @var string
     */
    public $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $params = null)
    {
        $this->shop = $params['shop'];
        $this->token = $params['token'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $services = app('SCRIPT_TAG_SERVICE')->checkScriptTags([
            'shop' => $this->shop,
            'token' => $this->token
        ]);
    }
}
