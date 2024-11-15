#!/bin/sh
cd /ngrok
echo "nameserver 8.8.8.8" >> /etc/resolv.conf && echo "nameserver 9.9.9.9" >> /etc/resolv.conf
./ngrok http 443 > /dev/null
sleep 30 #sleep on tunnel break
