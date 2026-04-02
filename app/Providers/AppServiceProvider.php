<?php

namespace App\Providers;

use App\Models\ProductionOrder;
use App\Observers\ProductionOrderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ProductionOrder::observe(ProductionOrderObserver::class);
    }
}
