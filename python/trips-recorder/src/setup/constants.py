import os
import logging

from utils.RandomGenerator import RandomStr

logging.basicConfig(format='%(asctime)s %(name)-14s %(levelname)-8s %(message)s',
                    level=logging.INFO,
                    datefmt='%Y-%m-%d %H:%M:%S')

LOGGER = logging.getLogger()

MQTT_USERNAME = RandomStr()
MQTT_PASSWORD = RandomStr()

MQTT_HOST = os.environ.get('MQTT_HOST', 'mosquitto')
MQTT_PORT = int(os.environ.get('MQTT_PORT', '1883'))

WP_TIMEOUT = 300
