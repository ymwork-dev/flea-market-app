# 土台となるイメージを指定
FROM php:8.3-fpm

# php.iniをコンテナ内に標準設定から上書き
COPY php.ini /usr/local/etc/php/php.ini

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# phpに標準で入っていない追加機能のインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composer公式イメージの実行ファイルをコピー
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# コマンド実行時の基準フォルダ
WORKDIR /var/www
