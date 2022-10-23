<?php

namespace App\Providers;

use Exception;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTelescope();
        $this->handleExceedingCumulativeQueryDuration();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function registerTelescope(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    private function handleExceedingCumulativeQueryDuration(): void
    {
        if (! app()->isProduction()) {
            DB::listen(static function (QueryExecuted $event) {
                if ($event->time > 100) {
                    throw new QueryException(
                        $event->sql,
                        $event->bindings,
                        new Exception('Individual database query exceeded 100ms.')
                    );
                }
            });
        }
    }
}
