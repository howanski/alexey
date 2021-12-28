#!/bin/sh
cd /ngrok
export $(cat .env.local | grep -v '#' | awk '/=/ {print $1}')

if [ ! -f ngrok ]; then
    wget https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip
    unzip ngrok-stable-linux-amd64.zip
fi
rm -f ngrok-stable-linux-amd64.zip
chmod +x ngrok
./ngrok authtoken $NGROK_AUTH_TOKEN

nohup supervisord
