"""Module defining the constants for the app"""

import os

WP_TIMEOUT = os.environ.get('WP_TIMEOUT', 300)
NOMINATIM_CACHE_TTL = os.environ.get('NOMINATIM_CACHE_TTL', 172800)
