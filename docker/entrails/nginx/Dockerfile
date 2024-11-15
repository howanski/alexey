FROM nginx:stable
COPY 40-nginx_startup_script.sh /docker-entrypoint.d/40-nginx_startup_script.sh
RUN apt update > /dev/null
RUN apt install -y tar wget curl jq supervisor htop > /dev/null
