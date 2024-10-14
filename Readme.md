# Alexey

Alexey is a small application I started developing as "Personal assistant" way before AI boom.

Current features are:
- weather forecast
- network devices monitoring/waking
- monitoring LTE usage (Mikrotik and Huawei routers - just a subset I personally had)
- storing financial details for analysis where did my money gone and how much I can expect to save by x time
- crawling through my favourite subreddits, looking for most popular articles
- basic acces via Android app and ability to open secure tunnel to log in via *unknown* browser using One Time Password

Matter of features is still open - I've no idea what I'm gonna need/want to include in this app.

For now it is a field for experiments and some fun with coding :)

## Installation

I love simple deployments. For development-ready instance all you need is Docker, Docker-Compose (v2) and Bash:

First of all - if you want Wake On LAN to work, you must configure network OUTSIDE containers in [docker-compose.yml](./docker/docker-compose.yml)

```yaml
...BLAH BLAH BLAH...
    wake_lan:
        driver: macvlan
        driver_opts:
            parent: enp3s0 #set your host network card here, i.e. eth0
        ipam:
            driver: default
            config:
                - subnet: 192.168.1.0/24 #set your network addressation here
...BLAH BLAH BLAH...
```

For production environment you may want to comment-out xdebug in [xdebug.ini](./docker/entrails/php-fpm/xdebug.ini) and set ```APP_ENV=prod``` in [env file](./docker/.env)
> By default xdebug is configured to work with vscode out of the box

If you also want to disable phpmyadmin for production environment you need to:

- remove below part from [docker-compose.yml](./docker/docker-compose.yml):

```yaml
    phpmyadmin:
        env_file:
            - .env
        image: phpmyadmin/phpmyadmin
        container_name: "alexey-phpmyadmin"
        expose:
            - 80
        networks:
            - network
        restart: always
        depends_on:
            - database
        logging:
          driver: json-file
          options:
              max-size: "5m"
              max-file: "2"
```
- remove below part from [php.conf](./docker/entrails/nginx/vhosts/php.conf):

```
  location /phpmyadmin/ {
    proxy_set_header X-Real-IP  $remote_addr;
    proxy_set_header X-Forwarded-For $remote_addr;
    proxy_set_header Host $host;
    proxy_pass http://phpmyadmin/;
    proxy_buffering off;
  }
```

And then it's a childsplay:
```bash
# go to docker catalogue
cd scripts
# run self-building application script
#(this one will run for a loooong time and will stick to your terminal so you'll see logs - open new terminal tab and continue typing commands after you'll see that logs stabilised)
#(I'm sure you'll feel it :-) )
./install
# get inside worker-container
./console_php
# generate certificate
./regenerate_https_certificate
#create user
php bin/console alexey:user:new
# and now open your browser on https://localhost/ to log in ;-)
```

Above scenario should work out-of-the-box if there are no port conflicts with your containers. Alexey containers will be up every time you turn on your computer.

## Deinstallation
```bash
cd scripts
./uninstall
# That's all :-) Well, if you want to be sure there's really nothing left, you can prune leftovers shared by all Docker Containers:
docker image prune
docker volume prune
```
If you prefer GUI, or just doesn't want to prune something important, there's great project I love to use: [Portainer](https://www.portainer.io/)

## Further information
This Readme is a stub just like Alexey as that's just a hobby project developed after long day of coding so it will be extended from time to time. Feel free to look around, script names should be self-explanatory.
