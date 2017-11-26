FROM ubuntu:16.04

RUN mkdir /app
WORKDIR /app

COPY docker_bootstrap.sh docker_bootstrap.sh
RUN chmod a+rwx /app/docker_bootstrap.sh
RUN /app/docker_bootstrap.sh

COPY docker-entrypoint.sh docker-entrypoint.sh
RUN chmod +x /app/docker-entrypoint.sh

COPY . .

ENTRYPOINT ["/app/docker-entrypoint.sh"]

