<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

// アプリが動き始めるときの設定を書くクラス
class AppServiceProvider extends ServiceProvider
{
    // アプリで使うサービスを登録するメソッド
    public function register(): void
    {
        // 今は何もしていない
    }

    // アプリが動き始める時に実行するメソッド
    public function boot(): void
    {
        // アプリが作るURLの先頭をDockerの外でも通じるようにいつも.envにそろえる
        URL::forceRootUrl(config('app.url'));
    }
}
