<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Stripeのwebhookは(ブラウザではなく)stripe-cliコンテナから
        // 直接nginxへ届くため、何もしないとメール内のリンクがDocker内部の
        // 名前(http://nginx/...)になってしまう。常に.envのAPP_URLを
        // 使うようにして、どこから来たリクエストでもリンクを統一する。
        URL::forceRootUrl(config('app.url'));
    }
}
