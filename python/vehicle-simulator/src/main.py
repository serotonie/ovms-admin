#!/usr/bin/env python3
import os
import time

from paho.mqtt.client import Client as MQTTClient

from redis_auth import build_redis_client, register_mqtt_superuser

BROKER_HOST = os.getenv("MQTT_BROKER_HOST", "mosquitto")
BROKER_PORT = int(os.getenv("MQTT_PORT", "1883"))
BROKER_USERNAME = os.getenv("MQTT_USERNAME", "admin-client")
BROKER_PASSWORD = os.getenv("MQTT_PASSWORD", "admin-client-secret")
CLIENT_ID = os.getenv("MQTT_CLIENT_ID", "ovms-backend-simulator")
COMMAND_TOPIC = os.getenv("MQTT_COMMAND_TOPIC", "ovms/+/+/client/+/command/+")
RESPONSE_DELAY_SECONDS = float(os.getenv("MQTT_RESPONSE_DELAY_SECONDS", "0.7"))


def ensure_mqtt_superuser() -> None:
    redis_client = build_redis_client()
    register_mqtt_superuser(redis_client, BROKER_USERNAME, BROKER_PASSWORD)
    print(f"Registered MQTT superuser for {BROKER_USERNAME} in Redis")


def build_response(command: str) -> str:
    normalized = (command or "").strip().lower()

    if normalized == "stat":
        return "Vehicle online; battery 85%; temperature 21C; odometer 12450km"

    if normalized in {"help", "?"}:
        return "Available commands: stat, help, ping"

    if normalized == "ping":
        return "PONG"

    return f"Mock vehicle response for: {command}"


def on_connect(client: MQTTClient, userdata, flags, rc, properties=None):
    if rc != 0:
        print(f"MQTT connection failed with code {rc}")
        return

    client.subscribe(COMMAND_TOPIC, qos=0)
    print(f"Connected to {BROKER_HOST}:{BROKER_PORT} and subscribed to {COMMAND_TOPIC}")


def on_message(client: MQTTClient, userdata, msg):
    topic = msg.topic
    command = msg.payload.decode("utf-8", errors="replace")
    response_topic = topic.replace("/command/", "/response/", 1)
    response_payload = build_response(command)

    print(f"Received command '{command}' on {topic}")
    time.sleep(RESPONSE_DELAY_SECONDS)
    client.publish(response_topic, response_payload, qos=0, retain=False)
    print(f"Published response '{response_payload}' to {response_topic}")


def main() -> None:
    ensure_mqtt_superuser()

    client = MQTTClient(client_id=CLIENT_ID)
    client.on_connect = on_connect
    client.on_message = on_message

    if BROKER_USERNAME:
        client.username_pw_set(BROKER_USERNAME, BROKER_PASSWORD)

    client.connect(BROKER_HOST, BROKER_PORT, keepalive=60)

    try:
        client.loop_forever(retry_first_connection=True)
    except KeyboardInterrupt:
        print("Shutting down vehicle simulator")
        client.disconnect()


if __name__ == "__main__":
    main()
