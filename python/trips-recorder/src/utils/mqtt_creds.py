"""Module to set the MQTT Creds for the trips recorder in REDIS"""
import logging

import bcrypt

from database.config import REDIS
from setup.constants import MQTT_USERNAME, MQTT_PASSWORD

def set_creds():
    """Set the MQTT Creds in REDIS"""
    redis_log = logging.getLogger('redis')
    redis_log.info('setting up creds for mqtt client')
    REDIS.set(MQTT_USERNAME, bcrypt.hashpw(MQTT_PASSWORD.encode('utf-8'), bcrypt.gensalt()), 300)
    REDIS.set(MQTT_USERNAME+":su", "true", 300)
