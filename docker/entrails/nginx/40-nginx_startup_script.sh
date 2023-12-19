#!/bin/sh
cd /ngrok
export $(cat .env.local | grep -v '#' | awk '/=/ {print $1}')

if [ ! -f ngrok ]; then
    wget https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-amd64.tgz
    tar -xvzf ngrok-v3-stable-linux-amd64.tgz
fi
rm -f ngrok-v3-stable-linux-amd64.tgz
chmod +x ngrok
./ngrok config add-authtoken $NGROK_AUTH_TOKEN

nohup supervisord
