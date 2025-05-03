<?php

use App\Jobs\CreateNewLeaderboard;
use App\Jobs\DeleteOldAirPollutionData;
use App\Jobs\StoreCurrentAirPollution;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Bus;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withSchedule(function (Schedule $schedule) {
//        $schedule->call(function () {
//            Bus::chain([
//                new StoreCurrentAirPollution,
//                new CreateNewLeaderboard
//            ])->dispatch();
//        })->everyThirtyMinutes();
//        $schedule->job(new DeleteOldAirPollutionData)->daily();
        $schedule->job(new StoreCurrentAirPollution)->everyThirtyMinutes();
        $schedule->job(new CreateNewLeaderboard)->everyThirtyMinutes();
    })->create();
