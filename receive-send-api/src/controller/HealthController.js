const express = require("express");
const router = express.Router();

router.get("/health", async (req, res) => {
  try {
    res.status(200).json({ status: "ok", message: "Service is running" });
  } catch (error) {
    console.error("Health check failed:", error);
    res.status(500).json({ status: "error", message: "Service is down" });
  }
});

module.exports = router;