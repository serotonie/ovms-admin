from resettabletimer import ResettableTimer

class ResettableTimerDaemon(ResettableTimer):
    def _set(self):
        super()._set()
        self._timer.daemon = True