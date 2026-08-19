import unittest

from redis_auth import register_mqtt_superuser


class FakeRedis:
    def __init__(self):
        self.store = {}

    def set(self, key, value, ex=None):
        self.store[key] = (value, ex)
        return True


class RedisAuthTests(unittest.TestCase):
    def test_register_mqtt_superuser_stores_hash_and_flag(self):
        redis_client = FakeRedis()

        register_mqtt_superuser(redis_client, "sim-user", "secret-pass")

        self.assertIn("sim-user", redis_client.store)
        self.assertIsNone(redis_client.store["sim-user"][1])
        self.assertIsNone(redis_client.store["sim-user:su"][1])
        self.assertEqual(redis_client.store["sim-user:su"][0], "true")
        self.assertNotEqual(redis_client.store["sim-user"][0], b"secret-pass")


if __name__ == "__main__":
    unittest.main()
