<?php

use Illuminate\Support\Facades\Schedule;

// デモ環境を毎日リセットし、閲覧者が編集・削除した内容を初期状態に戻す
Schedule::command('demo:reset')->daily();

