#! /bin/bash
set -e

ME=$(basename $0)

if [ -f  "/mosquitto/certs/live/example.com/chain.pem" ]; then
    echo "[$ME] The broker certs exists."
    echo "[$ME] Let's enable SSL/TLS and listen on port 8883 for MQTTS"
    export BROKER_SSL="#"
else
    echo "[$ME] Broker certs not found, this is dangerous in production !"
    echo "[$ME] Please mount your SSL/TLS certs according to the documentation"
    echo "[$ME] Defaulting to normal MQTT on port 1883"
    export BROKER_NO_SSL="#"
fi


echo "[$ME] Creating mosquito conf from ENV"

for f in $(find ./template -name "*.template")
do
    envsubst < $f > $(echo $f | sed 's=./template=/mosquitto/config=g' | sed 's=.template==g')
done

echo "[$ME] mosquitto conf created"

exec "$@" 2>&1 |
while read -r line; do
  case "$line" in
    *'New connection from 127.0.0.1:'*' on port '1880'.') ;; # drop
    *'New client connected from 127.0.0.1:'*' as healthcheck '?'p2, c1, k60'?'.') ;; # drop
    *'New client connected from 127.0.0.1:'*' as healthcheck '?'p2, c1, k60, u'"'"*"'"?'.') ;; # drop
    *'Client healthcheck disconnected.') ;; # drop
    *) echo "$line" ;; # forward
  esac
done
