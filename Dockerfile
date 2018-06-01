FROM ubuntu:16.04

RUN mkdir /app
WORKDIR /app

# Make symbolic link from /app to /var/www
RUN ln -fs /app /var/www

COPY container_install.sh container_install.sh
RUN chmod a+rwx /app/container_install.sh
RUN /app/container_install.sh

COPY docker-entrypoint.sh docker-entrypoint.sh
RUN chmod +x /app/docker-entrypoint.sh

COPY . .

ENTRYPOINT ["/app/docker-entrypoint.sh"]

