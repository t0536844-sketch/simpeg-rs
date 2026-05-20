FROM php:8.3-cli

# Install SQLite dev libraries needed for pdo_sqlite extension
RUN apt-get update && apt-get install -y libsqlite3-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_sqlite

WORKDIR /app

# Copy all app files
COPY . /app/

# Create data directory for SQLite with proper permissions
RUN mkdir -p data && chmod 777 data

# Set SQLite mode
ENV USE_SQLITE=true

EXPOSE 7860

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:7860", "-t", "."]
