FROM php:8.3-cli

RUN docker-php-ext-install pdo pdo_sqlite

WORKDIR /app

# Copy all app files
COPY . /app/

# Create data directory for SQLite
RUN mkdir -p data

# Set SQLite mode
ENV USE_SQLITE=true

EXPOSE 7860

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:7860", "-t", "."]
