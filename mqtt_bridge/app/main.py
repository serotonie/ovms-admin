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
            id: int = 0,
            gpshdop: float = -1,
            odometer: float = -1,
            latitude: float = -1,
            longitude: float = -1,
            trip: float = -1
        ):
        self.id = id
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
        if (hasattr(self, 'timer')) and (self.trip != -1):
            id = self.id
            props = self.__dict__.copy()
            props.pop('timer')
            r.publish("waypointFor_"+self.vehicle_id, json.dumps(props))
            print("new Waypoint from "+self.vehicle_id+":\n"+json.dumps(props))
            self.timer.reset()
            id = id + 1
            vehicles[self.vehicle_id]["lastWaypoint"] = self
            vehicles[self.vehicle_id]["currentWaypoint"] = Waypoint(self.vehicle_id, self.timer, id, self.gpshdop, self.odometer)
            
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

def on_waypoint_message(client, userdata, msg):
    vehicle_id = id_from_topic(msg.topic)
    if vehicles[vehicle_id].get("currentWaypoint") is not None:
        new_metric = msg.topic.split ("/")[-1]
        if (hasattr(vehicles[vehicle_id]["currentWaypoint"], new_metric)):
            setattr(vehicles[vehicle_id]["currentWaypoint"], new_metric, float(msg.payload))

def on_config_message(client, userdata, msg):
    print("Result from config: "+ id_from_topic(msg.topic)+" ["+msg.topic.split("/")[-1]+"]: "+str(msg.payload))
    client.message_callback_remove(msg.topic)
    client.unsubscribe(msg.topic)   

def on_missed_vehicle_on(client, userdata, msg):
    vehicle_id = id_from_topic(msg.topic)
    if(msg.payload == b'yes\n'):
        vehicle_on(vehicles[vehicle_id], client, msg)     
    client.message_callback_remove(msg.topic)
    client.unsubscribe(msg.topic)       

def vehicle_off(vehicle, client, msg):
    print(vehicle["id"]+" is off")
    r.publish("stopTrip_"+vehicle["id"], json.dumps({
        "timestamp": str(datetime.now(pytz.timezone('UTC'))),
        "trip": vehicle["lastWaypoint"].trip,
        "odometer": vehicle["lastWaypoint"].odometer,
        "latitude": vehicle["lastWaypoint"].latitude,
        "longitude": vehicle["lastWaypoint"].longitude
    }))
    waypoint_topic = msg.topic.replace("/event", "/metric/v/p/#")
    client.unsubscribe(waypoint_topic)
    client.message_callback_remove(waypoint_topic)
    if vehicle.get("currentWaypoint") is not None:
        vehicle["currentWaypoint"].timer.cancel()
        print(vehicle["currentWaypoint"].timer)
        del vehicle["currentWaypoint"]

def vehicle_on(vehicle, client, msg):
    if vehicle.get("currentWaypoint") is None:
        print(vehicle["id"]+" is on")
        waypoint_topic = msg.topic.replace("/event", "/metric/v/p/#").replace("/client/mqtt_bridge/response/missed_on", "/metric/v/p/#")
        print(waypoint_topic)
        client.subscribe(waypoint_topic, 2)
        client.message_callback_add(waypoint_topic, on_waypoint_message)
        vehicle["currentWaypoint"] = Waypoint(vehicle["id"],ResettableTimerDaemon(os.getenv('WAYPOINT_TIMEOUT', 300), vehicle_off, [vehicle, client, msg]))
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
    print("new Event from "+vehicle_id+":\n"+str(msg.payload))
    r.publish("msgFrom_"+vehicle_id, msg.payload)
    if (msg.payload == b'vehicle.on'):
        vehicle_on(vehicle, client, msg)
    if (msg.payload == b'vehicle.off'):
        vehicle_off(vehicle, client, msg)
    if (msg.payload == b'server.v3.connected'):
        vehicle_connected(vehicle, client, msg)
    if vehicle.get("currentWaypoint") is None:
        missed_vehicle_on_topic = msg.topic.replace("/event", "/client/mqtt_bridge")
        client.subscribe(missed_vehicle_on_topic+"/response/missed_on", 2)
        client.message_callback_add(missed_vehicle_on_topic+"/response/missed_on", on_missed_vehicle_on)
        client.publish(missed_vehicle_on_topic+"/command/missed_on", "metric get v.e.on")    
    

mqttc = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
mqttc.username_pw_set(mqtt_username, mqtt_password)
mqttc.on_connect = on_connect
mqttc.on_message = on_message

mqttc.connect(os.getenv('MQTT_HOST', 'mosquitto'), int(os.getenv('MQTT_PORT', '1883')))

mqttc.loop_forever()