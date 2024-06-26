from peewee import Model, BigAutoField, DateTimeField, CharField, FloatField, ForeignKeyField, IntegerField
from database.config import DB

class Base(Model):

    class Meta:
        database = DB

class Vehicle(Base):

    id = BigAutoField()
    last_seen = DateTimeField(null=True)
    module_id = CharField(unique=True)
    module_username = CharField(unique=True)

    class Meta:
        table_name = 'vehicles'

class Trip(Base):
    distance = FloatField()
    id = BigAutoField()
    start_point_lat = FloatField()
    start_point_long = FloatField()
    start_time = DateTimeField()
    stop_point_lat = FloatField(null=True)
    stop_point_long = FloatField(null=True)
    stop_time = DateTimeField()
    vehicle = ForeignKeyField(column_name='vehicle_id', field='id', model=Vehicle)

    class Meta:
        table_name = 'trips'

class Waypoint(Base):
    timestamp = DateTimeField(null=True)
    distance = FloatField()
    gpshdop = FloatField(null=True)
    id = BigAutoField()
    odometer = IntegerField()
    position_lat = FloatField()
    position_long = FloatField()
    trip = ForeignKeyField(column_name='trip_id', field='id', model=Trip)
    
    class Meta:
        table_name = 'waypoints'