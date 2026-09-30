#!/bin/sh
set -e

# ① Renderが渡してくるポート番号を、nginxの設定の目印と置き換える
sed -i "s/PORT_PLACEHOLDER/${PORT:-10000}/" /etc/nginx/http.d/default.conf

# ② Laravelの設定・ルート・画面をまとめておき、動作を軽くする
php artisan optimize

# ③ データベースのテーブルを最新の状態にする
php artisan migrate --force

# ④ PHPを裏で起動する
php-fpm -D

# ⑤ nginxを起動する（これが動いている間、コンテナが動き続ける）
exec nginx -g 'daemon off;'