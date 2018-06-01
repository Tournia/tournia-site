#!/usr/bin/env bash

# Make symbolic link from /app to /var/www (but only if that hasn't been done)
if [ ! -L "/var/www" ]; then
  rm -rf /var/www
  ln -fs /app /var/www
fi

# Run container install script
container_install.sh
