import logging

from threading import Timer
from datetime import datetime

from nanoid import generate

import database.models as models
import mqtt_callbacks as callbacks

from utils.ResettableTimerDaemon import ResettableTimerDaemon
from setup.constants import WP_TIMEOUT, LOGGER

class Vehicle():
    def __init__(self, module_id, client) -> None:
        self.log = logging.getLogger(module_id)
        self.model = models.Vehicle.get_or_none(models.Vehicle.module_id == module_id)
        self.mqttc = client
        self.command = {}
        self.driving = ''
        self.current_trip = models.Trip.select().where(models.Trip.vehicle == self.model.id).where(models.Trip.stop_time.is_null()).get_or_none()
        if self.current_trip is not None:
            self.current_waypoint = Waypoint(
                log=self.log,
                trip_id=self.current_trip,
                timer=ResettableTimerDaemon(WP_TIMEOUT, setattr, [self, 'driving', 'no'])
                )
            self.current_waypoint.timer.start()
        else:
            self.current_waypoint = Waypoint(log=self.log)
        for attr in self.current_waypoint.__dict__.keys():
            if attr == 'trip_id' or attr == 'timer' or attr == 'last_waypoint':
                pass
            elif attr != 'utc':
                topic = "ovms/+/" + self.model.module_id + "/metric/v/p/" + attr
                self.mqttc.message_callback_add(topic, callbacks.on_wp_update)
                self.mqttc.subscribe(topic, 2)
            else:
                topic = "ovms/+/" + self.model.module_id + "/metric/m/time/" + attr
                self.mqttc.message_callback_add(topic, callbacks.on_wp_update)
                self.mqttc.subscribe(topic, 2)
        self.send_command('metrics get v.e.on', 'vehicle_state(msg.payload, vehicles[id])')
    
    def __del__(self):
        for attr in self.current_waypoint.__dict__.keys():
            if attr == 'trip_id' or attr == 'timer' or attr == 'last_waypoint':
                pass
            elif attr != 'utc':
                topic = "ovms/+/" + self.model.module_id + "/metric/v/p/" + attr
                self.mqttc.unsubscribe(topic)
                self.mqttc.message_callback_remove(topic)
                
            else:
                topic = "ovms/+/" + self.model.module_id + "/metric/m/time/" + attr
                self.mqttc.unsubscribe(topic)
                self.mqttc.message_callback_remove(topic)

        for command_id in self.command:
            self.cancel_command(command_id)

    def __setattr__(self, name, value) -> None:
        if name == 'driving':
            match value:
                case 'yes':
                    if value != self.driving:
                        self.log.info('New driving state: yes')
                        self.start_trip()
                case 'no':
                    if value != self.driving:
                        self.log.info('New driving state: no')
                        self.stop_trip()
        object.__setattr__(self, name, value)

    def send_command(self, command, callback):
        command_id = generate(size=10)

        command_topic = '/'.join((
            'ovms',
            self.model.module_username,
            self.model.module_id,
            'client',
            'python',
            'command',
            command_id))
        
        response_topic ='/'.join((
            'ovms',
            self.model.module_username,
            self.model.module_id,
            'client',
            'python',
            'response',
            command_id))
        
        self.command[command_id] = {
            'command': command,
            'response_topic': response_topic,
            'callback': callback,
            'timer': Timer(10, self.timeout_command, args=[command_id])
        }

        self.mqttc.publish(command_topic, command, 2)
        self.mqttc.message_callback_add(response_topic, callbacks.on_command_response)
        self.mqttc.subscribe(response_topic, 2)
        self.command[command_id]['timer'].start()

    def timeout_command(self, command_id):
        self.log.info(command_id + ' TIMEOUT: don\'t received a response on ' + self.command[command_id]['command'])
        self.cancel_command(command_id)

    def cancel_command(self, command_id):
        self.mqttc.unsubscribe(self.command[command_id]['response_topic'], 2)
        self.mqttc.message_callback_remove(self.command[command_id]['response_topic'])
        self.mqttc.publish(self.command[command_id]['response_topic'], "", 0)
        self.mqttc.publish(self.command[command_id]['response_topic'].replace("response", "command"), "", 0)
        self.command[command_id]['timer'].cancel()
        self.command.pop(command_id)       

    def start_trip(self):
        if self.current_trip is not None:
            pass
        else:
            self.current_trip = models.Trip.create(
                vehicle=self.model,
                start_time=datetime.now(),
                start_point_lat=self.current_waypoint.latitude,
                start_point_long=self.current_waypoint.longitude,
                distance=self.current_waypoint.trip
                )
        self.current_waypoint.trip_id = self.current_trip
        self.current_waypoint.timer = ResettableTimerDaemon(WP_TIMEOUT, setattr, [self, 'driving', 'no'])
        self.current_waypoint.timer.start()

    def stop_trip(self):
        if self.current_trip is not None:
            self.current_trip.stop_time=datetime.now()
            self.current_trip.stop_point_lat=self.current_waypoint.last_waypoint.position_lat
            self.current_trip.stop_point_long=self.current_waypoint.last_waypoint.position_long
            self.current_trip.distance=self.current_waypoint.last_waypoint.distance
            self.current_trip.save()
            self.current_trip = None
            self.current_waypoint.trip_id = -1
            self.current_waypoint.timer.cancel()
            self.current_waypoint.timer = -1

class Waypoint():
    def __init__(self,
                    log,
                    utc = '1970-01-01 00:00:00 UTC',
                    longitude = -1,
                    latitude = -1,
                    gpshdop = -1,
                    odometer = -1,
                    trip = -1,
                    trip_id = -1,
                    timer = -1,
                    last_waypoint={}
                    ) -> None:    
        self.last_waypoint=last_waypoint
        self.trip_id = trip_id
        self.timer = timer
        self.utc = utc
        self.longitude = longitude
        self.latitude = latitude
        self.gpshdop = gpshdop
        self.odometer = odometer
        self.trip = trip
        self.log = log

    def __setattr__(self, name, value) -> None:
        match name:
            case 'utc':
                object.__setattr__(self, name, datetime.strptime(value, '%Y-%m-%d %H:%M:%S %Z'))
            case 'trip':
                object.__setattr__(self, name, value)
                if value != -1:
                    if self.trip_id != -1 and self.latitude != -1 and self.longitude != -1:
                        current_waypoint = models.Waypoint.create(
                            timestamp = self.utc,
                            distance = self.trip,
                            gpshdop = self.gpshdop,
                            odometer = self.odometer,
                            position_lat = self.latitude,
                            position_long = self.longitude,
                            trip = self.trip_id
                        )
                        self.log.debug(current_waypoint.__dict__)
                        self.timer.reset()
                        self.__init__(
                            log=self.log,
                            gpshdop=self.gpshdop,
                            odometer=self.odometer,
                            trip_id=self.trip_id,
                            timer=self.timer,
                            last_waypoint=current_waypoint
                            )
            case _:
                if value != '(not found)':
                    if not hasattr(self, name) or value != getattr(self, name):
                        object.__setattr__(self, name, value)

    
