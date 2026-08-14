"""Module defining the constants for the app"""

import logging
import os

WP_TIMEOUT = os.environ.get('WP_TIMEOUT', 300)
NOMINATIM_CACHE_TTL = os.environ.get('NOMINATIM_CACHE_TTL', 172800)
LOG_LEVEL_NAME = os.environ.get('LOG_LEVEL', 'INFO').upper()
LOG_LEVEL = getattr(logging, LOG_LEVEL_NAME, logging.INFO)
