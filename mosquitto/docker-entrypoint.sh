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

exec "$@"
