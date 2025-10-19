#!/bin/bash

# Verificar se estamos em modo de desenvolvimento (com volumes)
if [ -d "/var/www/html/vendor" ] && [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Executando composer install em ambiente de desenvolvimento..."
    composer install --no-interaction --prefer-dist
fi

# Verificar se o JWT Auth está instalado
if [ ! -f "/var/www/html/vendor/tymon/jwt-auth/src/Providers/LaravelServiceProvider.php" ]; then
    echo "JWT Auth não encontrado, instalando dependências..."
    composer install --no-interaction --prefer-dist
fi

# Criando um database padrão (SQLite)
touch database/database.sqlite

# Garantir permissões corretas
echo "Configurando permissões..."
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Definir permissões para diretórios importantes
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/database

# Permissões mais abertas para desenvolvimento
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database

# Configurar variáveis de ambiente para PostgreSQL
export DB_CONNECTION=pgsql
export DB_HOST=postgres
export DB_PORT=5432
export DB_DATABASE=${DB_DATABASE:-plataforma}
export DB_USERNAME=${DB_USERNAME:-plataforma_user}
export DB_PASSWORD=${DB_PASSWORD:-plataforma_password}

# Executar comandos Laravel
echo "Gerando chave da aplicação..."
php artisan key:generate --no-interaction

echo "Executando migrations..."
php artisan migrate --no-interaction

echo "Criando link de storage..."
php artisan storage:link --no-interaction

echo "Gerando JWT secret..."
php artisan jwt:secret --no-interaction

# Verificar se precisa executar seeders
echo "Verificando se precisa executar seeders..."

if [ ! -f "/var/www/html/storage/.seeded" ]; then
    echo "Executando seeders pela primeira vez..."
    php artisan db:seed --no-interaction
    
    # Marcar como já executado
    touch /var/www/html/storage/.seeded
    echo "Seeders executados com sucesso!"
else
    echo "Seeders já foram executados anteriormente."
    echo "Para forçar execução novamente, delete o arquivo: storage/.seeded"
fi

# Iniciar o Supervisor
exec supervisord -c /etc/supervisord.conf