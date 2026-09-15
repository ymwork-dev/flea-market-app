<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

//アプリ起動時にウェブルートとArtisanコマンドルートの場所と
//アプリが正しく起動したか確認できるヘルスチェックURLの場所を設定
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    //未ログインユーザーと、ログイン済みユーザーのリダイレクト先設定
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(
            guests: '/login',
            users: '/',
        );

        // 2. 自作ミドルウェアの登録
        $middleware->alias([
            'ensure.profile.completed' => \App\Http\Middleware\EnsureProfileIsCompleted::class,
            'ensure.verified.profile' => \App\Http\Middleware\EnsureVerifiedProfileIfLoggedIn::class,
            'restrict.demo.account' => \App\Http\Middleware\RestrictDemoAccount::class,
        ]);

        // 3. StripeのWebhookはCSRFトークンを持たずに送られてくるので、対象外にする
        //    (代わりに署名の確認をコントローラー側で行っている)
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
