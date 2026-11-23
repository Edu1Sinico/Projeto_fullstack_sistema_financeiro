#!/bin/sh
set -e

# Cria as tabelas no MySQL da nuvem (usa CREATE TABLE IF NOT EXISTS, então
# rodar de novo a cada deploy não apaga nada). Para desligar: DB_AUTO_INSTALL=false
if [ "${DB_AUTO_INSTALL:-true}" = "true" ]; then
    php /var/www/html/database/install.php || echo "Aviso: não foi possível preparar o banco agora."
fi

exec docker-php-entrypoint "$@"
