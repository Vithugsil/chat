const express = require("express");
const bodyParser = require("body-parser");
const MessageController = require("./controller/MessageController");
const WorkerController = require("./controller/WorkerController");
const ReadController = require("./controller/ReadController");

const app = express();
app.use(bodyParser.json());

app.use("/", MessageController);
app.use("/", WorkerController);
app.use("/", ReadController);

const PORT = 8002;
app.listen(PORT, () => {
  console.log(`Receive-Send-API rodando na porta ${PORT}`);
});
