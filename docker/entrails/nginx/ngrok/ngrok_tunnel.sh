#!/bin/sh
cd /ngrok
echo "nameserver 8.8.8.8" >> /etc/resolv.conf && echo "nameserver 9.9.9.9" >> /etc/resolv.conf
if [ -n "$NGROK_URL" ]; then
    ./ngrok http --url=$NGROK_URL 443 > /dev/null
else
    ./ngrok http 443 > /dev/null
fi
sleep 30 #sleep on tunnel break
