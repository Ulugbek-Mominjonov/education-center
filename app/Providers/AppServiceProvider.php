<?php

namespace App\Providers;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\StudentRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);

        Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
            Log::debug($query->sql . ' - ' . serialize($query->bindings));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
