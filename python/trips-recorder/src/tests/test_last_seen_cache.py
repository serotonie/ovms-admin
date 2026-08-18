import os
import sys
from datetime import datetime

sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

from utils import last_seen as last_seen_module


class DummyTimer:
    def __init__(self, *args, **kwargs):
        self.args = args
        self.kwargs = kwargs

    def daemonic(self):
        return False

    def start(self):
        return None


def test_cache_last_seen_stores_value_in_redis(monkeypatch):
    calls = {}

    class DummyRedis:
        def set(self, key, value, ex=None):
            calls['key'] = key
            calls['value'] = value
            calls['ex'] = ex

    monkeypatch.setattr(last_seen_module, 'REDIS', DummyRedis())
    monkeypatch.setattr(last_seen_module, '_PENDING_UPDATES', {})
    monkeypatch.setattr(last_seen_module, '_FLUSH_TIMER', None)
    monkeypatch.setattr(last_seen_module, 'Timer', DummyTimer)

    timestamp = datetime(2024, 1, 1, 12, 0, 0)
    last_seen_module.cache_last_seen('veh-1', timestamp)

    assert calls['key'] == 'vehicle:last_seen:veh-1'
    assert calls['value'] == timestamp.isoformat()
    assert last_seen_module._PENDING_UPDATES['veh-1'] == timestamp
