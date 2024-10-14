# Use the official PHP 8.3 image
FROM php:8.3-cli

# Set the working directory
WORKDIR /app

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP extensions if needed (e.g., mysqli, pdo_mysql, etc.)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set the entry point for PHPUnit
ENTRYPOINT ["vendor/bin/phpunit"]
