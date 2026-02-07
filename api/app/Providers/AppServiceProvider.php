<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    protected $policies = [
        \App\Models\Account::class => \App\Policies\AccountPolicy::class,
        \App\Models\Withdrawal::class => \App\Policies\WithdrawalPolicy::class,
        \App\Models\Deposit::class => \App\Policies\DepositPolicy::class,
        \App\Models\Bar::class => \App\Policies\BarPolicy::class,
    ];

}
