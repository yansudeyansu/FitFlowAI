FROM php:8.2-apache

# システムの依存関係をインストール
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip

# PHPの拡張機能をインストール
RUN docker-php-ext-install pdo pdo_mysql

# Composerのインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Apacheの設定
RUN a2enmod rewrite

# 作業ディレクトリを設定
WORKDIR /var/www/html

# Apacheのユーザーを変更
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Apacheを前景で実行
CMD ["apache2-foreground"]