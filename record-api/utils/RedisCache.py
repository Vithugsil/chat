import redis
from app_config import Config

class RedisCache:
    _client = None

    @classmethod
    def get_client(cls):
        if cls._client is None:
            cls._client = redis.Redis(
                host=Config.REDIS_HOST,
                port=Config.REDIS_PORT,
                decode_responses=True
            )
        return cls._client

    @classmethod
    def get(cls, key):
        return cls.get_client().get(key)

    @classmethod
    def set(cls, key, value, ttl=60):
        return cls.get_client().setex(key, ttl, value)
