#!/bin/sh
cd /ngrok
curl http://localhost:4040/api/tunnels | jq ".tunnels[0].public_url" > ngrok.log
sleep 30
