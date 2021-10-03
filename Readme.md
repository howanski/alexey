# Alexey

Alexey is a small application I started developing as "Personal personal assistant", which means it's assistant that's perfect for me - have all the functions I need, and stores data on my infrastructure.

Matter of features is still open - I'd like to include invoicing, taxes, state observer, IoT manager, cameras center, an on, and on, and on...

For now it serves as an experimenting field for Symfony 5 and Docker capabilities.

## Installation

I love simple deployments. For development-ready instance all you need is Docker, Docker-Compose and Bash:

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
#create user
php bin/console alexey:user:new
# and now open your browser on localhost and accept suspicious key ;-)
```
Above scenario should work out-of-the-box if there are no port conflicts with your containers. Alexey containers will be up every time you turn on your computer.

## Deinstallation
```bash
cd docker
./uninstall
# That's all :-) Well, if you want to be sure there's really nothing left, you can prune leftovers shared by all Docker Containers:
docker image prune
docker volume prune
```
If you prefer GUI, or just doesn't want to prune something important, there's great project I love to use: [Portainer](https://www.portainer.io/)

## Further information
This Readme is a stub just like Alexey as that's just a hobby project developed after long day of coding so it will be extended from time to time. Feel free to look around, script names should be self-explanatory.
