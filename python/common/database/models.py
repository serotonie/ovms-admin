"""Module defining the peewee models"""

from peewee import (
    Model,
    BigAutoField,
    DateTimeField,
    CharField,
    ForeignKeyField,
)
from playhouse.signals import Model, pre_save # pylint: disable=reimported
from common.database.config import DB

class Base(Model):
    """Defining a Base Model"""

    class Meta:
        """Metadata of Base Model"""
        database = DB
        only_save_dirty=True

class User(Base):
    """Model of user"""
    id = BigAutoField()

    class Meta:
        """Metadata of """
        table_name = 'users'

class Vehicle(Base):
    """Model of vehicle"""
    id = BigAutoField()
    last_seen = DateTimeField(null=True)
    module_id = CharField(unique=True)
    module_username = CharField(unique=True)
    main_user_id = ForeignKeyField(model=User, field='id')

    class Meta:
        """Metadata of vehicle model"""
        table_name = 'vehicles'