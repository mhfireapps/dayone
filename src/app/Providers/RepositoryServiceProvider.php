<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\AuthRepository;
use App\Repositories\ShopsRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\BaseRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BaseRepository::AUTH, function ($app) {
            return new AuthRepository($app);
        });

        $this->app->bind(BaseRepository::SHOP, function ($app) {
            return new ShopsRepository($app);
        });
        $this->app->bind(BaseRepository::CUSTOMER, function ($app) {
            return new CustomerRepository($app);
        });

        $this->app->bind(BaseRepository::ORDER, function ($app) {
            return new OrderRepository($app);
        });

        $this->app->bind(BaseRepository::PRODUCT, function ($app) {
            return new ProductRepository($app);
        });
    }
}
