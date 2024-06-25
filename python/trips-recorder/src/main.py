import logging
import bcrypt

import paho.mqtt.client as mqtt

from database.config import REDIS
from setup.constants import MQTT_USERNAME, MQTT_PASSWORD, MQTT_HOST, MQTT_PORT
import mqtt_callbacks as callbacks

redis_log = logging.getLogger('redis')
redis_log.debug('setting up creds for mqtt client')
REDIS.set(MQTT_USERNAME, bcrypt.hashpw(MQTT_PASSWORD.encode('utf-8'), bcrypt.gensalt()))
REDIS.set(MQTT_USERNAME+":su", "true")

mqtt_log = logging.getLogger('mqtt')
MQTTC = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
MQTTC.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
MQTTC.enable_logger(mqtt_log)
MQTTC.on_connect = callbacks.on_connect
MQTTC.on_message = callbacks.on_message

vehicles = {}

MQTTC.user_data_set(vehicles)
MQTTC.connect(MQTT_HOST, MQTT_PORT)



MQTTC.loop_forever()