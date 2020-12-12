<?php

namespace App\Providers;

use App\Contracts\MasterDataStore;
use App\MasterData\Store;
use Illuminate\Support\ServiceProvider;

class MasterDataServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->bind(MasterDataStore::class, Store::class);
    }

    public function provides()
    {
        return [
            MasterDataStore::class,
        ];
    }
}
