"""Module defining the callbacks for the mqtt client"""

import logging
from datetime import datetime

from common.utils.mqtt_creds import set_creds
from common.utils.topic import vhc_id_from_topic

log = logging.getLogger('mqtt')

def on_connect(client, userdata, flags, reason_code, properties): # pylint: disable=unused-argument
    """Mqtt Callback when client is connected"""
    log.info("Connected with result code %s", reason_code)
    client.subscribe("ovms/+/+/event", 2)

def on_connect_fail(client, userdata): # pylint: disable=unused-argument
    """Mqtt Callback when client can't connect"""
    log.warning('Failed to connect to mqtt broker, I will retry')

def on_message(client, userdata, msg):
    """Default mqtt callback when a message is received"""
    log.info(msg)
