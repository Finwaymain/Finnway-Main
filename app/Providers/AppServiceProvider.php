<?php

namespace App\Providers;

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
        setcookie('XSRF-TOKEN-AK', bin2hex(config('app.firebase.apikey')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-AD', bin2hex(config('app.firebase.auth_domain')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-DU', bin2hex(config('app.firebase.database_url')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-PI', bin2hex(config('app.firebase.project_id')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-SB', bin2hex(config('app.firebase.storage_bucket')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-MS', bin2hex(config('app.firebase.messaging_sender_id')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-AI', bin2hex(config('app.firebase.app_id')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-MI', bin2hex(config('app.firebase.measurement_id')), time() + 3600, "/"); 
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Pagination\Paginator::defaultView('pagination.pagination');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('tj_currency')) {
                $sym = \App\Helpers\Helper::getCurrencySymbol();
                \Illuminate\Support\Facades\View::share('currency_symbol', $sym);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\View::share('currency_symbol', '₹');
        }
    }
}
