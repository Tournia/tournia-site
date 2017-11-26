#!/bin/sh
set -e

echo "This file will be executed on every start"

# MySQL configuration
usermod -d /var/lib/mysql/ mysql
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld
service mysql restart

# Create MySQL user and database if it hasn't been created before
if [ ! -f /var/log/databasesetup ];
then
    QUERY="CREATE DATABASE IF NOT EXISTS \`tournament\`; GRANT ALL ON \`tournament\`.* to 'tournament'@'%' identified by 'tournament';FLUSH PRIVILEGES;"
    mysql -uroot -pvagrant -e "$QUERY"
    php /var/www/app/console doctrine:migrations:migrate -n
    mysql -utournament -ptournament tournament < /app/testDump.sql

    echo "sql_mode = STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >> /etc/mysql/mysql.conf.d/mysqld.cnf

    touch /var/log/databasesetup
fi

# Update composer
cd /var/www
php composer.phar install

# Run migrations (again) when redoing provisioning
php /var/www/app/console doctrine:migrations:migrate -n

# Create symlink to assets
php app/console assets:install public_html --symlink

rm -rfd /var/tournia -R
mkdir /var/tournia
mkdir /var/tournia/cache
mkdir /var/tournia/logs
chown www-data:www-data /var/tournia -R

service apache2 restart

echo "Tournia is ready!"
while true; do sleep 1; done
