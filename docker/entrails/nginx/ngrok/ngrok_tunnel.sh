#!/bin/sh
cd /ngrok
./ngrok http 443 > /dev/null
sleep 30 #sleep on tunnel break
