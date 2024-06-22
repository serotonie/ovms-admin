import logging
from datetime import datetime

from utils.Topic import IdFromTopic
from classes import Vehicle

from setup.constants import LOGGER

log = logging.getLogger('mqtt')

def on_connect(client, userdata, flags, reason_code, properties):
    log.info(f"Connected with result code {reason_code}")
    client.subscribe("ovms/+/+/event", 2)

def on_message(client, vehicles, msg):
    id = IdFromTopic(msg.topic)
    if not vehicles.__contains__(id):
        vehicles = vehicles | {IdFromTopic(msg.topic): Vehicle(id, client)}
        client.user_data_set(vehicles)

    vehicles[id].model.last_seen = datetime.now()
    vehicles[id].model.save()

    if msg.payload == b'vehicle.on':
        vehicles[id].driving = 'yes'

    if msg.payload == b'vehicle.off':
        vehicles[id].driving = 'no'

def on_command_response(client, vehicles, msg):
    id = IdFromTopic(msg.topic)
    command_id = msg.topic.split('/')[-1]
    exec(vehicles[id].command[command_id]['callback'])
    vehicles[id].cancel_command(client, msg.topic, command_id)

def on_wp_update(client, vehicles, msg):
    id = IdFromTopic(msg.topic)
    current_wp_update(msg.payload, vehicles[id], msg.topic.split('/')[-1])

def current_wp_update(payload, vehicle, attr):
    setattr(vehicle.current_waypoint, attr, payload.decode().strip().replace('km', ''))

def vehicle_state(payload, vehicle):
    vehicle.driving = payload.decode().strip()