# ===== 第1段階: CSS・JSをビルドする =====
FROM node:20-alpine AS assets
WORKDIR /app
COPY src/package.json src/package-lock.json ./
RUN npm ci
COPY src/ ./
RUN npm run build

# ===== 第2段階: 本番で動かすイメージ =====
FROM php:8.3-fpm-alpine

# nginxと、MySQLにつなぐためのPHP拡張を入れる
RUN apk add --no-cache nginx unzip
RUN docker-php-ext-install pdo_mysql

# Composerを入れる
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# アプリのコードを入れて、本番に必要なパッケージだけインストールする
WORKDIR /var/www/html
COPY src/ ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 第1段階でビルドしたCSS・JSだけを持ってくる
COPY --from=assets /app/public/build ./public/build

# デモ用の商品画像を、seederと同じようにstorageへコピーしておく
RUN mkdir -p storage/app/public/items && cp public/img/dummy/*.jpg storage/app/public/items/

# public/storage から storage/app/public への近道を作る（画像をURLで見せるため）
RUN php artisan storage:link

# PHPが書き込むフォルダの持ち主を、PHPの実行ユーザーにする
RUN chown -R www-data:www-data storage bootstrap/cache

# ステップ1・2で作ったファイルを置く
COPY docker/render/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/render/start.sh /start.sh

# AivenのCA証明書を置く（本番のDBと暗号化してつなぐときに使う）
COPY docker/render/aiven-ca.pem /etc/ssl/aiven-ca.pem

CMD ["sh", "/start.sh"]
