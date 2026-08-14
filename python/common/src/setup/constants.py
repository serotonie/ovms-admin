"""Module defining the constants for everything in common"""

import logging
import os

from ..utils.random_generator import random_str

LOG_LEVEL_NAME = os.environ.get('LOG_LEVEL', 'INFO').upper()
LOG_LEVEL = getattr(logging, LOG_LEVEL_NAME, logging.INFO)

logging.basicConfig(
    format='%(asctime)s %(name)-14s %(levelname)-8s %(message)s',
    level=LOG_LEVEL,
    datefmt='%Y-%m-%d %H:%M:%S'
)

LOGGER = logging.getLogger()

MQTT_USERNAME = random_str()
MQTT_PASSWORD = random_str()

MQTT_HOST = os.environ.get('MQTT_HOST', 'mosquitto')
MQTT_PORT = int(os.environ.get('MQTT_PORT', '1883'))

MQTT_CREDS_REFRESH = os.environ.get('MQTT_CREDS_REFRESH', 300)