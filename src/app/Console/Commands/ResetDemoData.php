<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'デモ用データベースを初期状態(シード直後の状態)に戻す';

    public function handle(): void
    {
        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    }
}
