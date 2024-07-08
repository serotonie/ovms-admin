"""Module defining the constants for the app"""

import os
import logging

from utils.random_generator import random_str

logging.basicConfig(format='%(asctime)s %(name)-14s %(levelname)-8s %(message)s',
                    level=logging.os.environ.get('LOG_LEVEL', 'INFO').upper(),
                    datefmt='%Y-%m-%d %H:%M:%S')

LOGGER = logging.getLogger()

MQTT_USERNAME = random_str()
MQTT_PASSWORD = random_str()

MQTT_HOST = os.environ.get('MQTT_HOST', 'mosquitto')
MQTT_PORT = int(os.environ.get('MQTT_PORT', '1883'))

WP_TIMEOUT = os.environ.get('WP_TIMEOUT', 300)
MQTT_CREDS_REFRESH = os.environ.get('MQTT_CREDS_REFRESH', 300)

NOMINATIM_CACHE_TTL = os.environ.get('NOMINATIM_CACHE_TTL', 172800)
