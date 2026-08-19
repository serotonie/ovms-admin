import os

import bcrypt
import valkey as redis


def build_redis_client():
    return redis.Redis(
        os.getenv("REDIS_HOST", "redis"),
        os.getenv("REDIS_PORT", "6379"),
        int(os.getenv("REDIS_DB", "2")),
        os.getenv("REDIS_PASSWORD"),
    )


def register_mqtt_superuser(redis_client, mqtt_username, mqtt_password):
    redis_client.set(
        mqtt_username,
        bcrypt.hashpw(mqtt_password.encode("utf-8"), bcrypt.gensalt()),
    )
    redis_client.set(f"{mqtt_username}:su", "true")
