# Imagem do Finaxis para o Render (PHP + Apache, igual ao XAMPP).
FROM php:8.3-apache

# Extensão do MySQL (PDO) e o mod_rewrite usado pelo .htaccess.
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers

# Configuração do Apache (porta do Render, .htaccess liberado e pastas internas bloqueadas).
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/finaxis-entrypoint

# Copia o sistema. O .dockerignore impede que o .env local entre na imagem:
# no Render, as configurações vêm das variáveis de ambiente.
COPY . /var/www/html/

# Corrige quebras de linha do Windows (CRLF) e ajusta permissões.
RUN sed -i 's/\r$//' /usr/local/bin/finaxis-entrypoint \
    && chmod +x /usr/local/bin/finaxis-entrypoint \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && sed -i 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf \
    && chown -R www-data:www-data /var/www/html

# O Render informa a porta pela variável PORT (padrão 10000).
ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["finaxis-entrypoint"]
CMD ["apache2-foreground"]
