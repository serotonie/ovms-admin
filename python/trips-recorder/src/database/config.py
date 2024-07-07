"""Module to configure the databases used"""

import os
import valkey as redis
from peewee import Model
from playhouse.db_url import connect

DB = connect(''.join((
    'mysql' if (os.getenv('DB_CONNECTION', 'mariadb') == 'mariadb') else os.getenv('DB_CONNECTION'),
    '://',
    os.getenv('DB_USERNAME'),
    ':',
    os.getenv('DB_PASSWORD'),
    '@',
    os.getenv('DB_HOST', 'mariadb'),
    ':',
    os.getenv('DB_PORT', '3306'),
    '/',
    os.getenv('DB_DATABASE', 'ovms-admin')))
)

REDIS = redis.Redis(
        os.getenv('REDIS_HOST', 'redis'),
        os.getenv('REDIS_PORT','6379'),
        int(os.getenv('REDIS_DB','2')),
        os.getenv('REDIS_PASSWORD')
)
