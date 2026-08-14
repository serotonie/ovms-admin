"""Common package shim for the flattened shared modules."""

import logging
from os.path import dirname

from setup.constants import LOG_LEVEL

logging.basicConfig(
    level=LOG_LEVEL,
    format='%(asctime)s %(levelname)s %(name)s: %(message)s'
)

__path__ = [dirname(__file__)]