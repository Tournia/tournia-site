#!/usr/bin/env bash

# Make symbolic link from /vagrant to /var/www (but only if that hasn't been done)
if [ ! -L "/var/www" ]; then
  rm -rf /var/www
  ln -fs /vagrant /var/www
fi

# Run container install script
chmod a+x /vagrant/container_install.sh
/vagrant/container_install.sh

# Fix upload problem
chown -R vagrant /var/lib/phpmyadmin/tmp
chgrp -R vagrant /var/lib/phpmyadmin/tmp

# Useful for Docker
rm -rfd /var/tournia -R
mkdir /var/tournia
mkdir /var/tournia/cache
mkdir /var/tournia/logs
chown www-data:www-data /var/tournia -R

# Create MySQL user and database if it hasn't been created before
if [ ! -f /var/log/databasesetup ];
then
    QUERY="CREATE DATABASE IF NOT EXISTS \`tournament\`; GRANT ALL ON \`tournament\`.* to 'tournament'@'%' identified by 'tournament';FLUSH PRIVILEGES;"
    mysql -uroot -pvagrant -e "$QUERY"
    php /var/www/app/console doctrine:migrations:migrate -n
    mysql -utournament -ptournament tournament < /vagrant/testDump.sql

    echo "sql_mode = STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >> /etc/mysql/mysql.conf.d/mysqld.cnf

    touch /var/log/databasesetup
fi

# Update composer
cd /var/www
php composer.phar update

chmod a+rwx /var/tournia -R
chown www-data /var/tournia -R

## Run migrations (again) when redoing provisioning
php /var/www/app/console doctrine:migrations:migrate -n

# Create symlink to assets
php app/console assets:install public_html --symlink
