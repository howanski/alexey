FROM nginx:stable
COPY 40-nginx_startup_script.sh /docker-entrypoint.d/40-nginx_startup_script.sh
RUN echo "nameserver 8.8.8.8" >> /etc/resolv.conf && echo "nameserver 9.9.9.9" >> /etc/resolv.conf
RUN apt update > /dev/null
RUN apt install -y tar wget curl jq supervisor htop > /dev/null
