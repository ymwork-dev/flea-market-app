<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. 未ログイン時のリダイレクト先設定
        $middleware->redirectTo(
            guests: '/login',
            users: '/',
        );

        // 2. 自作ミドルウェアの登録（ここを追記！）
        $middleware->alias([
            'ensure.profile.completed' => \App\Http\Middleware\EnsureProfileIsCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
