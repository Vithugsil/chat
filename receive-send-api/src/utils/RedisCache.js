const Redis = require('ioredis');

class RedisCache {
  constructor() {
    if (!RedisCache.instance) {
      RedisCache.instance = new Redis({
        host: 'redis',
        port: 6379
      });
    }
    this.client = RedisCache.instance;
  }

  async get(key) {
    return await this.client.get(key);
  }

  async set(key, value, ttlSeconds = 60) {
    // ttl em segundos
    return await this.client.setex(key, ttlSeconds, value);
  }
}

module.exports = RedisCache;
