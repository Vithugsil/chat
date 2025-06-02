const Redis = require("ioredis");

class RedisCache {
  constructor() {
    if (!RedisCache.instance) {
      RedisCache.instance = new Redis({
        host: process.env.Redis_Host || "redis",
        port: process.env.Redis_port || 6379,
      });
    }
    this.client = RedisCache.instance;
  }

  async get(key) {
    return await this.client.get(key);
  }

  async set(key, value, ttlSeconds = 60) {
    return await this.client.setex(key, ttlSeconds, value);
  }
}

module.exports = RedisCache;
