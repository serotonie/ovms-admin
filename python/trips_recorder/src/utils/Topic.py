from functools import cache

@cache
def IdFromTopic(topic):
    return topic.split('/')[2]