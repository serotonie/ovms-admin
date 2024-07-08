"""Module to set the MQTT Creds for the mqtt client in REDIS"""
import logging

import bcrypt

from utils.resettabletimer_daemon import ResettableTimerDaemon
from database.config import REDIS
from setup.constants import MQTT_USERNAME, MQTT_PASSWORD, MQTT_CREDS_REFRESH

def set_creds():
    """Set the MQTT Creds in REDIS"""
    redis_log = logging.getLogger('redis')
    redis_log.debug('setting up creds for mqtt client')
    REDIS.set(
        MQTT_USERNAME,
        bcrypt.hashpw(
            MQTT_PASSWORD.encode('utf-8'),
            bcrypt.gensalt()
        ),
        MQTT_CREDS_REFRESH
    )
    REDIS.set(MQTT_USERNAME+":su", "true", MQTT_CREDS_REFRESH*2)
    timer = ResettableTimerDaemon(MQTT_CREDS_REFRESH, set_creds)
    timer.start()
