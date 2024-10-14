"""Main module"""

import logging

import sys
sys.path.insert(0, '../..')

import paho.mqtt.client as mqtt
import mqtt_callbacks as callbacks

from common.utils.mqtt_creds import set_creds
from common.setup.constants import MQTT_USERNAME, MQTT_PASSWORD, MQTT_HOST, MQTT_PORT

set_creds()

mqtt_log = logging.getLogger('mqtt')
MQTTC = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
MQTTC.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
MQTTC.enable_logger(mqtt_log)
MQTTC.on_connect = callbacks.on_connect
MQTTC.on_connect_fail = callbacks.on_connect_fail
MQTTC.on_message = callbacks.on_message

vehicles = {}

MQTTC.user_data_set(vehicles)
MQTTC.connect(MQTT_HOST, MQTT_PORT)

MQTTC.loop_forever()
