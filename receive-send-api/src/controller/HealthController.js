const express = require("express");
const router = express.Router();
const redisCache = require("../utils/RedisCache");

router.get("/health", async (req, res) => {
  try {
    const redis = new redisCache();
    const isRedisHealthy = await redis.client.ping();
    if (!isRedisHealthy) {
      console.error("Redis is not responding");
      return res
        .status(503)
        .json({ status: "error", message: "Redis is down" });
    }
    res.status(200).json({ status: "ok", message: "Service is running" });
  } catch (error) {
    console.error("Health check failed:", error);
    res.status(500).json({ status: "error", message: "Service is down" });
  }
});

module.exports = router;
