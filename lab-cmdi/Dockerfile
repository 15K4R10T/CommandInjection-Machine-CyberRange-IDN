FROM php:8.1-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    default-mysql-server \
    supervisor \
    iputils-ping \
    net-tools \
    curl \
    nmap \
    dnsutils \
    && docker-php-ext-install mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY web/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html/ && \
    rm -f /var/www/html/index.html

# Buat file target untuk challenge
RUN mkdir -p /var/private && \
    printf "FLAG{cmdi_basic_rce}\n" > /var/private/flag.txt && \
    printf "DB_HOST=127.0.0.1\nDB_USER=labuser\nDB_PASS=labpass123\nSECRET=FLAG{cmdi_config_read}\n" > /var/private/config.txt && \
    printf "root:x:0:0:root:/root:/bin/bash\nwww-data:x:33:33:www-data:/var/www:/usr/sbin/nologin\nlabuser:x:1000:1000::/home/labuser:/bin/bash\n" > /var/private/shadow.bak && \
    chmod 644 /var/private/flag.txt /var/private/config.txt /var/private/shadow.bak

COPY init.sql /docker-entrypoint-initdb.sql
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
