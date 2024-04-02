import paho.mqtt.client as mqtt
import redis
import bcrypt
import os
import json
import pytz

from datetime import datetime

from resettabletimer import ResettableTimer

from RandomGenerator import RandomStr

default_config = [
    "config set auto server.v3 yes",
    "config set notify report.trip.enable yes",
    "config set server.v3 updatetime.charging 5",
    "config set vehicle stream 5"
]

mqtt_username = RandomStr()
mqtt_password = RandomStr()

vehicles = dict()

class ResettableTimerDaemon(ResettableTimer):
    def _set(self):
        super()._set()
        self._timer.daemon = True

class Waypoint():
    def __init__(
            self,
            vehicle_id: str,
            timer: ResettableTimerDaemon,
            self_id: int = 0,
            gpshdop: float = -1,
            latitude: float = -1,
            longitude: float = -1,
            odometer: int = -1,
            trip: float = -1
        ):
        self.self_id = self_id
        self.gpshdop = gpshdop
        self.latitude = latitude
        self.longitude = longitude
        self.odometer = odometer
        self.trip = trip
        self.vehicle_id = vehicle_id
        self.timer = timer
        if not (self.timer._running):
            self.timer.start()
        
    def __setattr__(self, name, value):
        super().__setattr__(name, value)
        if not (-1 in self.__dict__.values()): 
            if (hasattr(self, 'vehicle_id')) and (hasattr(self, 'timer')):
                if ((self.self_id == 0) and (self.trip < 0.2)):
                    self.self_id += 1
                elif (self.self_id > 0):
                    props = self.__dict__.copy()
                    props.pop('timer')
                    r.publish("waypointFor_"+self.vehicle_id, json.dumps(props))
                    print("new Waypoint from "+self.vehicle_id+":\n"+json.dumps(props))
                    self.self_id += 1
                    self.timer.reset()
                    vehicles[self.vehicle_id]["currentWaypoint"] = Waypoint(self.vehicle_id, self.timer, self.self_id)
            
r = redis.Redis(
    os.getenv('REDIS_HOST', 'redis'),
    os.getenv('REDIS_PORT','6379'),
    int(os.getenv('REDIS_DB','2')),
    os.getenv('REDIS_PASSWORD')
)
r.set(mqtt_username, bcrypt.hashpw(mqtt_password.encode('utf-8'), bcrypt.gensalt()))
r.set(mqtt_username+":su", "true")

if (os.getenv('APP_ENV')=='local'):
    r.set('MQTTExplorer', bcrypt.hashpw(b"FeL6Ayhg27m44yh7H4DvT", bcrypt.gensalt()))
    r.set('MQTTExplorer:su', "true")

def id_from_topic(topic):
    return topic.split("/")[2]

# The callback for when the client receives a CONNACK response from the server.
def on_connect(client, userdata, flags, reason_code, properties):
    print(f"Connected with result code {reason_code}")
    # Subscribing in on_connect() means that if we lose the connection and
    # reconnect then subscriptions will be renewed.
    client.subscribe("ovms/+/+/event", 2)
    client.subscribe("ovms/+/+/metric/v/e/on", 2)

def on_waypoint_message(client, userdata, msg):
    vehicle_id = id_from_topic(msg.topic)
    new_metric = msg.topic.split ("/")[-1]
    if (hasattr(vehicles[vehicle_id]["currentWaypoint"], new_metric)):
        setattr(vehicles[vehicle_id]["currentWaypoint"], new_metric, float(msg.payload))

def on_config_message(client, userdata, msg):
    print("message_config")
    print(id_from_topic(msg.topic)+" ["+msg.topic.split("/")[-1]+"]: "+str(msg.payload))
    client.message_callback_remove(msg.topic)
    client.unsubscribe(msg.topic)           

def vehicle_off(vehicle, client, msg):
    print(vehicle["id"]+" is off")
    waypoint_topic = msg.topic.replace("/event", "/metric/v/p/#")
    client.unsubscribe(waypoint_topic)
    client.message_callback_remove(waypoint_topic)
    del vehicle["currentWaypoint"]
    r.publish("stopTrip_"+vehicle["id"], str(datetime.now(pytz.timezone('UTC'))))

def vehicle_on(vehicle, client, msg, ALLREADY_ON=False):
    print(vehicle["id"]+" is on")
    waypoint_topic = msg.topic.replace("/event", "/metric/v/p/#")
    client.subscribe(waypoint_topic, 2)
    client.message_callback_add(waypoint_topic, on_waypoint_message)
    vehicle["currentWaypoint"] = Waypoint(vehicle["id"],ResettableTimerDaemon(os.getenv('WAYPOINT_TIMEOUT', 300), vehicle_off, [vehicle, client, msg]), int(ALLREADY_ON))
    r.publish("startTrip_"+vehicle["id"], str(datetime.now(pytz.timezone('UTC'))))

def vehicle_connected(vehicle, client, msg):
    print(vehicle["id"]+" is connected")
    for count, value in enumerate(default_config):
        config_topic = msg.topic.replace("/event", "/client/mqtt_bridge")
        client.subscribe(config_topic+"/response/"+str(count), 2)
        client.message_callback_add(config_topic+"/response/"+str(count), on_config_message)
        client.publish(config_topic+"/command/"+str(count), value)
        print(vehicle["id"]+" ["+str(count)+"]: "+value)

# The callback for when a PUBLISH message is received from the server.
def on_message(client, userdata, msg):
    vehicle_id = id_from_topic(msg.topic)
    vehicles.setdefault(vehicle_id, {})
    vehicle = vehicles[vehicle_id]
    vehicle["id"] = vehicle_id
    if(msg.topic.split("/")[-1] == "on"): 
        if ((msg.payload == "yes") and (vehicle.get("currentWaypoint") is None)):
            vehicle_on(vehicle, client, msg, True)
    else:
        print("new Event from "+vehicle_id+":\n"+str(msg.payload))
        r.publish("msgFrom_"+vehicle_id, msg.payload)
        if (msg.payload == b'vehicle.on'):
            vehicle_on(vehicle, client, msg)
        if (msg.payload == b'vehicle.off'):
            vehicle_off(vehicle, client, msg)
        if (msg.payload == b'server.v3.connected'):
            vehicle_connected(vehicle, client, msg)
    

mqttc = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
mqttc.username_pw_set(mqtt_username, mqtt_password)
mqttc.on_connect = on_connect
mqttc.on_message = on_message

mqttc.connect(os.getenv('MQTT_HOST', 'mosquitto'), int(os.getenv('MQTT_PORT', '1883')))

mqttc.loop_forever()